import express from "express";
import { buildTaxRouter } from "./routes/taxRoutes.js";

export function createApp() {
  const app = express();

  app.use(express.json());

  app.get("/health", (_req, res) => {
    res.status(200).json({ ok: true, service: "shopify-country-tax-app" });
  });

  app.use("/api/tax", buildTaxRouter());

  app.use((err, _req, res, _next) => {
    if (err?.name === "ZodError") {
      return res.status(400).json({
        error: "Invalid request payload",
        details: err.issues,
      });
    }

    return res.status(500).json({ error: "Unexpected server error" });
  });

  return app;
}

export const app = createApp();
