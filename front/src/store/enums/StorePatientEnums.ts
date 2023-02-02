const PatientActions = {
  LIST: "listPatients",
  CREATE: "createPatients",
  DELETE: "deletePatients",
  UPDATE: "updatePatients",
  VIEW: "viewPatient",
  APPOINTMENTS: "patientsAppointments",
  DOCUMENTS: {
    LIST: "listPatientsDocuments",
    CREATE: "createPatientsDocument",
    SEND_VIA_EMAIL: "sendPatientsDocumentViaEmail",
    SEND_VIA_HEALTH_LINK: "sendPatientsDocumentViaHealthLink",
    VIEW: "viewPatientDocument",
    AUDIO: {
      CREATE: "createPatientsDocumentAudio",
    },
  },
  RECALL: {
    LIST: "listPatientsRecall",
    CREATE: "createPatientsRecall",
    UPDATE: "updatePatientsRecall",
  },
  ALERT: {
    CREATE: "createPatientAlert",
    UPDATE: "updatePatientAlert",
  },
  ALLERGY: {
    ADD: "addPatientAllergy",
    UPDATE: "updatePatientAllergy",
    DELETE: "deletePatientAllergy",
  },
  MEDICATION: {
    ADD: "addPatientMedication",
    UPDATE: "updatePatientMedication",
    DELETE: "deletePatientMedication",
  },
  CLAIM_SOURCE: {
    VALIDATE_MEDICARE: "validatePatientsBillingMedicare",
    VALIDATE_CONCESSION: "validatePatientsBillingConcession",
    VALIDATE_HEALTH_FUND: "validatePatientsBillingHealthFund",
    VALIDATE_DVA: "validatePatientsBillingDva",
    ADD: "addPatientsBilling",
    UPDATE: "updatePatientsBilling",
    DELETE: "deletePatientsBilling",
  },
  ALSO_KNOWN_AS: {
    CREATE: "createPatientAlsoKnownAs",
    UPDATE: "updatePatientAlsoKnownAs",
    BULK: "bulkPatientAlsoKnownAs",
    DELETE: "deletePatientAlsoKnownAs",
  },
  ALLERGIES_LIST: "getAllergiesList",
  PRINT_LABEL: "getPatientPrintLabel",
};

const PatientMutations = {
  SET_PATIENT: {
    LIST: "setPatientsList",
    SELECT: "setSelectPatient",
    APPOINTMENTS: "setPatientAppointments",
    DOCUMENTS: {
      LIST: "setPatientDocumentList",
    },
  },

  SET_PATIENT_RECALL: {
    LIST: "setPatientsRecallList",
    SELECT: "setSelectPatientRecall",
  },

  CLAIM_SOURCE: {
    SET_VALIDATION: "setClaimSourceValidation",
  },
};

export { PatientActions, PatientMutations };
