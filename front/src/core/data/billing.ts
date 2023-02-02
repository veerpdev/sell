export const convertToCurrency = (amount) => {
  let total = 0;

  if (!isNaN(parseFloat(amount))) {
    total = amount;
  }

  return new Intl.NumberFormat("en-AU", {
    style: "currency",
    currency: "AUD",
  }).format(total);
};

export const isMedicareValidationEnabled = () => {
  const isEnabled = process.env.VUE_APP_ENABLE_MEDICARE_VALIDATION;
  if (isEnabled && isEnabled == "false") {
    return false;
  }

  return true;
};
