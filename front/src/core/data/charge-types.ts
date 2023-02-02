interface IChargeTypes {
  value: string;
  label: string;
}

export const chargeTypes: Array<IChargeTypes> = [
  {
    value: "self-insured",
    label: "Self-insured",
  },
  {
    value: "private-health-excess",
    label: "Private Health Insurance with excess",
  },
  {
    value: "private-health-excess-0",
    label: "Private Health Insurance with $0 excess",
  },
  {
    value: "private-health-pension",
    label: "Private Health Insreance + Pension/Healthcare Card",
  },
  {
    value: "pension-card",
    label: "Pension Card",
  },
  {
    value: "healthcare-card",
    label: "Healthcare Card",
  },
  {
    value: "department-veteran",
    label: "Department of Veteran Affairs",
  },
  { value: "work-cover", label: "Work Cover" },
  {
    value: "transport-accident",
    label: "Transport Accident Commission",
  },
];

const payment_tier = {
  self_insured: "payment_tier_1",
  private_health_excess: "payment_tier_2",
  private_health_excess_0: "payment_tier_3",
  private_health_pension: "payment_tier_4",
  pension_card: "payment_tier_5",
  healthcare_card: "payment_tier_6",
  department_veteran: "payment_tier_7",
  work_cover: "payment_tier_8",
  transport_accident: "payment_tier_9",
};

export const getProcedurePrice = (data, charge_type, healthFund = null) => {
  let amount = 0;

  if (Object.prototype.hasOwnProperty.call(data, "procedures")) {
    data.procedures.forEach((item) => {
      let price = 0;

      if (item.price && !isNaN(parseFloat(item.price))) {
        price = parseFloat(item.price);
      }

      if (charge_type.includes("health-fund")) {
        const scheduleFee = item.schedule_fees.find(
          (fee) => fee.health_fund_code == healthFund
        );

        if (scheduleFee !== null) {
          item.price = scheduleFee.amount / 100;
          price = scheduleFee.amount / 100;
        }
      }

      amount += price;
    });
  }

  if (Object.prototype.hasOwnProperty.call(data, "extra_items")) {
    data.extra_items.forEach((item) => {
      let price = parseFloat(item.price);

      if (charge_type.includes("health-fund")) {
        const scheduleFee = item.schedule_fees.find(
          (fee) => fee.health_fund_code == healthFund
        );

        if (scheduleFee !== null) {
          item.price = scheduleFee.amount / 100;
          price = scheduleFee.amount / 100;
        }
      }

      amount += price;
    });
  }

  if (Object.prototype.hasOwnProperty.call(data, "admin_items")) {
    data.admin_items.forEach((item) => {
      let price = parseFloat(item.price);

      if (charge_type.includes("health-fund")) {
        const scheduleFee = item.schedule_fees.find(
          (fee) => fee.health_fund_code == healthFund
        );

        if (scheduleFee !== null) {
          item.price = scheduleFee.amount / 100;
          price = scheduleFee.amount / 100;
        }
      }

      amount += price;
    });
  }

  return amount;
};

export const getChargeTypes = (patient) => {
  const charges = [] as Array<IChargeTypes>;
  let hasMedicare = false;
  let hasHealthFund = false;
  let hasDva = false;

  charges.push({
    value: "self-insured",
    label: "Self-Insured",
  });

  patient.billings.forEach((source) => {
    if (source.is_valid) {
      switch (source.billing_type) {
        case 1:
          if (hasMedicare === false) {
            charges.push({
              value: "medicare",
              label: "Medicare Only",
            });
            hasMedicare = true;
          }
          break;
        case 2:
          if (hasHealthFund === false) {
            charges.push({
              value: "health-fund",
              label: "Health Fund Only",
            });
            hasHealthFund = true;
          }
          break;
        case 3:
          if (hasDva === false) {
            charges.push({
              value: "dva",
              label: "Department of Veteran Affairs",
            });
            hasDva = true;
          }
          break;
      }
    }
  });

  if (hasMedicare && hasHealthFund) {
    charges.push({
      value: "medicare-health-fund",
      label: "Medicare and Health Fund",
    });
  }

  return charges;
};

export default chargeTypes;
