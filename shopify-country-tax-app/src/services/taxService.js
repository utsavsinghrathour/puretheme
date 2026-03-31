import { fallbackTaxRule, getTaxRule, listTaxRules } from "../config/taxRules.js";

const money = (value) => Number(value.toFixed(2));

function normalizeCountryCode(countryCode) {
  return typeof countryCode === "string" ? countryCode.trim().toUpperCase() : null;
}

export function calculateTax({
  countryCode,
  subtotal,
  shipping = 0,
  currency,
  taxExempt = false,
  includeShippingInTaxableBase,
}) {
  const normalized = normalizeCountryCode(countryCode);
  const resolvedRule = normalized ? getTaxRule(normalized) : fallbackTaxRule;
  const rule = {
    countryCode: normalized,
    ...resolvedRule,
  };
  const shouldIncludeShipping =
    includeShippingInTaxableBase === undefined
      ? rule.includesShipping
      : includeShippingInTaxableBase;

  const taxableAmount =
    taxExempt || rule.rate === 0
      ? 0
      : subtotal + (shouldIncludeShipping ? shipping : 0);

  const taxAmount = money(taxableAmount * rule.rate);
  const totalAmount = money(subtotal + shipping + taxAmount);

  return {
    countryCode: rule.countryCode,
    taxLabel: rule.label,
    taxType: rule.kind,
    taxRate: rule.rate,
    currency: currency ?? rule.currency,
    subtotal: money(subtotal),
    shipping: money(shipping),
    taxableAmount: money(taxableAmount),
    taxAmount,
    totalAmount,
    taxExempt,
    includeShippingInTaxableBase: shouldIncludeShipping,
  };
}

export function listSupportedCountries() {
  return listTaxRules();
}
