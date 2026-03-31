import express from "express";
import { z } from "zod";
import { calculateTax } from "../services/taxService.js";
import { listTaxRules, setTaxRule } from "../config/taxRules.js";

const currencyOrEmpty = z
  .string()
  .trim()
  .length(3)
  .optional()
  .transform((value) => (value ? value.toUpperCase() : undefined));

const calculateTaxSchema = z.object({
  countryCode: z
    .string()
    .trim()
    .length(2)
    .transform((value) => value.toUpperCase()),
  subtotal: z.number().nonnegative(),
  shipping: z.number().nonnegative().optional().default(0),
  currency: currencyOrEmpty,
  taxExempt: z.boolean().optional().default(false),
  includeShippingInTaxableBase: z.boolean().optional(),
});

const updateTaxRuleSchema = z.object({
  countryCode: z
    .string()
    .trim()
    .length(2)
    .transform((value) => value.toUpperCase()),
  label: z.string().trim().min(1),
  kind: z.enum(["GST", "VAT", "SALES_TAX", "DUTY", "NONE"]),
  rate: z.number().nonnegative(),
  includesShipping: z.boolean().optional(),
  exemptionThreshold: z.number().nonnegative().optional(),
  currency: z
    .string()
    .trim()
    .length(3)
    .transform((value) => value.toUpperCase())
    .optional(),
  taxable: z.boolean().optional(),
});

export function buildTaxRouter() {
  const router = express.Router();

  router.get("/tax/rules", (_req, res) => {
    return res.json({ rules: listTaxRules() });
  });

  router.post("/tax/rules", (req, res) => {
    const parsed = updateTaxRuleSchema.safeParse(req.body);

    if (!parsed.success) {
      return res.status(400).json({
        error: "Invalid tax rule payload",
        details: parsed.error.flatten(),
      });
    }

    const { countryCode, ...rule } = parsed.data;
    const savedRule = setTaxRule(countryCode, rule);
    return res.status(201).json({
      countryCode,
      ...savedRule,
    });
  });

  router.post("/tax/calculate", (req, res) => {
    const parsed = calculateTaxSchema.safeParse(req.body);

    if (!parsed.success) {
      return res.status(400).json({
        error: "Invalid payload",
        details: parsed.error.flatten(),
      });
    }

    const result = calculateTax(parsed.data);
    return res.json(result);
  });

  router.post("/tax/shopify/carrier-service/rates", (req, res) => {
    const body = req.body?.rate ?? {};

    const parsed = calculateTaxSchema.safeParse({
      countryCode: body?.destination?.country,
      subtotal: Number(body?.items_subtotal_price ?? 0) / 100,
      shipping: Number(body?.shipping ?? 0),
      currency: body?.currency,
      taxExempt: false,
      includeShippingInTaxableBase: false,
    });

    if (!parsed.success) {
      return res.status(400).json({
        error: "Invalid Shopify rate payload",
        details: parsed.error.flatten(),
      });
    }

    const taxResult = calculateTax(parsed.data);
    return res.json({
      rates: [
        {
          service_name: "Estimated Tax",
          service_code: "estimated-tax",
          total_price: Math.round(taxResult.taxAmount * 100),
          description: `${taxResult.taxLabel} estimate`,
          currency: taxResult.currency,
        },
      ],
    });
  });

  return router;
}

export const taxRouter = buildTaxRouter();
