import request from "supertest";
import { describe, expect, it } from "vitest";
import { createApp } from "../src/app.js";

describe("Tax API", () => {
  const app = createApp();

  it("returns VAT for Germany", async () => {
    const response = await request(app).post("/api/tax/quote").send({
      countryCode: "DE",
      subtotal: 100,
      shipping: 10,
      currency: "EUR",
    });

    expect(response.statusCode).toBe(200);
    expect(response.body.countryCode).toBe("DE");
    expect(response.body.taxType).toBe("VAT");
    expect(response.body.taxRate).toBe(0.19);
    expect(response.body.taxableAmount).toBe(110);
    expect(response.body.taxAmount).toBe(20.9);
    expect(response.body.totalAmount).toBe(130.9);
  });

  it("returns GST for India", async () => {
    const response = await request(app).post("/api/tax/quote").send({
      countryCode: "IN",
      subtotal: 1000,
      shipping: 0,
      currency: "INR",
    });

    expect(response.statusCode).toBe(200);
    expect(response.body.taxType).toBe("GST");
    expect(response.body.taxRate).toBe(0.18);
    expect(response.body.taxAmount).toBe(180);
    expect(response.body.totalAmount).toBe(1180);
  });

  it("returns no tax for unknown countries", async () => {
    const response = await request(app).post("/api/tax/quote").send({
      countryCode: "AQ",
      subtotal: 250,
      shipping: 0,
      currency: "USD",
    });

    expect(response.statusCode).toBe(200);
    expect(response.body.taxType).toBe("NONE");
    expect(response.body.taxRate).toBe(0);
    expect(response.body.taxAmount).toBe(0);
    expect(response.body.totalAmount).toBe(250);
  });

  it("returns 400 for invalid payload", async () => {
    const response = await request(app).post("/api/tax/quote").send({
      countryCode: "USA",
      subtotal: -10,
      currency: "US",
    });

    expect(response.statusCode).toBe(400);
    expect(response.body.error).toBe("Invalid payload");
  });

  it("lists configured country rules", async () => {
    const response = await request(app).get("/api/tax/rules");
    expect(response.statusCode).toBe(200);
    expect(Array.isArray(response.body.rules)).toBe(true);
    expect(response.body.rules.length).toBeGreaterThan(0);
  });

  it("allows adding or updating a country rule", async () => {
    const update = await request(app).post("/api/tax/rules").send({
      countryCode: "ZA",
      taxName: "South Africa VAT",
      kind: "VAT",
      rate: 0.15,
      includesShipping: true,
      currency: "ZAR",
      taxable: true,
    });

    expect(update.statusCode).toBe(200);
    expect(update.body.countryCode).toBe("ZA");
    expect(update.body.rule.rate).toBe(0.15);

    const quote = await request(app).post("/api/tax/quote").send({
      countryCode: "ZA",
      subtotal: 200,
      shipping: 10,
      currency: "ZAR",
    });

    expect(quote.statusCode).toBe(200);
    expect(quote.body.taxType).toBe("VAT");
    expect(quote.body.taxAmount).toBe(31.5);
  });
});
