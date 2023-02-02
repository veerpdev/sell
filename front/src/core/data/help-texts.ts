/* eslint-disable prettier/prettier */
export const helpTexts = {
  forms: {
    appointmentType: {
      name: 
        "The name of this appointment type",
      type: 
        "The activity to be undertaken during this appointment",
      color:
        "The color that this appointment will appear in the appointment book",
      invoicedBy: 
      "Who will the invoice for this appointment be addressed from",
      anesthetistRequired:
        "Whether or not an anesthetist required for this appointment",
      collecting_person_required: 
        "Whether or not an person is required to collect this patient post appointment",
      arrivalTime:
        "How long before the begin of this appointment should the patient arrive",
      appointmentTime:
        "How long this appointment will take relative your organizations appointment length",
      defaultBillingItems:
        "The items that will usually be billed for this appointment type."+
        "<br /> These can be adjusted on a per appointment basis",
      preProcedureInstructions:
        "These instructions will be sent to the patient upon procedure approval",
      consent:
        "This section can be used to set the consent for this specific appointment type."+
        "<br /> If left blank, the consent will be the organization default",
    },
  },
};

export default helpTexts;
