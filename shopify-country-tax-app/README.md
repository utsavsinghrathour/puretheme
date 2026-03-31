# Shopify Country Tax App

A standalone Node.js service for calculating GST, VAT, and other taxes by
customer country for Shopify storefront and middleware flows.

Project location:

`/workspace/shopify-country-tax-app`

This keeps your existing repository files untouched and avoids overwriting your
current code.

## Quick start

```bash
cd /workspace/shopify-country-tax-app
npm install
cp .env.example .env
npm run dev
```

Server runs on `http://localhost:3000` by default.

## API endpoints

### Health

`GET /health`

### List configured country rules

`GET /api/tax/rules`

### Calculate tax by country

`POST /api/tax/quote`

Request body:

```json
{
  "countryCode": "DE",
  "subtotal": 100,
  "shipping": 10,
  "currency": "EUR",
  "taxExempt": false,
  "includeShippingInTaxableBase": true
}
```

Response example:

```json
{
  "countryCode": "DE",
  "taxLabel": "Germany VAT",
  "taxType": "VAT",
  "taxRate": 0.19,
  "currency": "EUR",
  "subtotal": 100,
  "shipping": 10,
  "taxableAmount": 110,
  "taxAmount": 20.9,
  "totalAmount": 130.9,
  "taxExempt": false,
  "includeShippingInTaxableBase": true
}
```

### Shopify carrier-service style estimate (optional)

`POST /api/tax/shopify/carrier-service/rates`

This endpoint accepts a Shopify-like rate payload and returns one "Estimated
Tax" line that can be used in custom rate workflows.

### Add or override a country rule at runtime

`POST /api/tax/rules`

Request body:

```json
{
  "countryCode": "ZA",
  "label": "South Africa VAT",
  "kind": "VAT",
  "rate": 0.15,
  "includesShipping": true,
  "currency": "ZAR",
  "taxable": true
}
```

## Default country coverage

Rules in `src/config/taxRules.js` include:

- AU, NZ, IN, CA (GST variants)
- GB, DE, FR (VAT variants)
- US (example sales-tax style rule)

Unknown countries default to no tax.

## Run tests

```bash
npm test
```

## Production hardening suggestions

- Add request authentication/HMAC validation for Shopify calls
- Persist tax rules in a database
- Add audit logging
- Deploy behind HTTPS with rate-limiting
