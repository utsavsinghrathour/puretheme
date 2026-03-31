function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

export const defaultTaxRules = {
  AU: {
    label: "Australia GST",
    kind: "GST",
    rate: 0.1,
    includesShipping: true,
    exemptionThreshold: 0,
    currency: "AUD",
    taxable: true,
  },
  NZ: {
    label: "New Zealand GST",
    kind: "GST",
    rate: 0.15,
    includesShipping: true,
    exemptionThreshold: 0,
    currency: "NZD",
    taxable: true,
  },
  IN: {
    label: "India GST",
    kind: "GST",
    rate: 0.18,
    includesShipping: true,
    exemptionThreshold: 0,
    currency: "INR",
    taxable: true,
  },
  DE: {
    label: "Germany VAT",
    kind: "VAT",
    rate: 0.19,
    includesShipping: true,
    exemptionThreshold: 0,
    currency: "EUR",
    taxable: true,
  },
  FR: {
    label: "France VAT",
    kind: "VAT",
    rate: 0.2,
    includesShipping: true,
    exemptionThreshold: 0,
    currency: "EUR",
    taxable: true,
  },
  GB: {
    label: "United Kingdom VAT",
    kind: "VAT",
    rate: 0.2,
    includesShipping: true,
    exemptionThreshold: 0,
    currency: "GBP",
    taxable: true,
  },
  CA: {
    label: "Canada GST/HST",
    kind: "GST",
    rate: 0.05,
    includesShipping: true,
    exemptionThreshold: 0,
    currency: "CAD",
    taxable: true,
  },
  US: {
    label: "United States Sales Tax (example)",
    kind: "SALES_TAX",
    rate: 0.07,
    includesShipping: false,
    exemptionThreshold: 0,
    currency: "USD",
    taxable: true,
  },
};

export const fallbackTaxRule = {
  label: "No Tax",
  kind: "NONE",
  rate: 0,
  includesShipping: false,
  exemptionThreshold: 0,
  currency: "USD",
  taxable: false,
};

const runtimeTaxRules = clone(defaultTaxRules);

export function getTaxRule(countryCode) {
  return runtimeTaxRules[countryCode] ?? fallbackTaxRule;
}

export function listTaxRules() {
  return Object.entries(runtimeTaxRules).map(([countryCode, rule]) => ({
    countryCode,
    ...rule,
  }));
}

export function setTaxRule(countryCode, rule) {
  runtimeTaxRules[countryCode] = {
    ...fallbackTaxRule,
    taxable: true,
    ...rule,
  };
  return runtimeTaxRules[countryCode];
}
