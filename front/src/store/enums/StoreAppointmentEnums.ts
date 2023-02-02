/* eslint-disable prettier/prettier */
const AppointmentActions = {


  LIST: "listAppointment",                 // (get)    'appointments'
  REFERRAL: {
    UPDATE:   "updateAppointmentReferral", // (put)    'appointments/referral/{appointment}'
    VIEW:     "viewAppointmentReferral",   // (post)   'appointments/referral/file'
  },
  APPOINTMENT_TYPES:{
    LIST:   "listAptTypes",               //  (get)    '/appointment-types'
    CREATE: "createAptTypes",             //  (post)   '/appointment-types'
    UPDATE: "updateAptTypes",             //  (put)    '/appointment-types/{appointmentType}'
    DELETE: "deleteAptTypes",             //  (delete) '/appointment-types/{appointmentType}'
  },
  PROCEDURE_APPROVAL: {
    UPDATE: "updateProcedureApproval",    //  (put)    '/appointment/procedure-approvals/{appointment}'
    UPLOAD: "uploadProcedureApproval",    //  (put)    '/appointment/procedure-approvals/upload/{appointment}'
  },
  CONFIRMATION_STATUS: {
    UPDATE: "updateConfirmationApt",      //  (put) '/appointments/confirmation-status/{appointment}'
  },
  COLLECTING_PERSON:{
    UPDATE: "updateCancellationApt",      //  (put) '/appointments/collecting-person/{appointment}'
  },
  PRE_ADMISSION: {
    VIEW:     "viewPreAdmissionForm",      // (post)   'appointment/procedure-approvals/pdf/{appointment}'
    ORGANIZATION: "listAptPreAdmissionOrg",
    VALIDATE:     "validateAptPreAdmission",
    STORE:        "createAptPreAdmission",
  },
  MONTH_AVAILABILITIES : "showMonthAvailabilities",
  DETAIL: {
    UPDATE: "updateAppointmentDetail",
  },

  // These are slowly being moved to the above
  APT: {
    CREATE: "createApt",
    DELETE: "deleteApt",
    UPDATE: "updateApt",
    CHECK_IN: "checkInApt",
    CHECK_OUT: "checkOutApt",
    DRAFT: {
      CREATE: "createDraftApt",
      DELETE: "deleteDraftApt",
    },
    BULK: {
      LIST: "listApt",
      UPDATE: "updateApts"
    }
  },
  BOOKING: {
    SEARCH: {
      SPECIALISTS: "searchBookingBySPT",
      NEXT_APT: "searchNextApt",
    },
  },
  RESEND_MESSAGE: 'resendConfirmMessage',

};

const AppointmentMutations = {

  SET_BOOKING: {
    LIST: "setBookingList",
    SELECT: "setSelectBooking",
    SEARCH: {
      VARIABLE: "setSearchVariable",
      DATE: "setFilteredBookingByDate",
      SPECIALISTS: "setFilteredBookingBySPT",
      FILLTEREDSPECIALISTS: "setFilteredBookingBySPT1",
      NEXT_APTS: "setNextAptList",
    },
  },
  DRAFT: {
    SET: "setAppointmentDraftId"
  },
  SET_APT: {
    LIST: "setApt",
    LISTBYID: "setAptById",
    SELECT: "setSelectApt",
    SELECT_SPECIALIST: "setSelectedSpecialist",
    TYPES: {
      LIST: "setAptType",
      SELECT: "setSelectAptType",
    },
    PRE_ADMISSION: {
      ORG: "setAptPreAdmissionOrg",
      VALIDATE: {
        DATA: "setValidateAptPreAdmission",
        MSG: "setValidateAptPreAdmissionMsg",
      },
    },
    USER_APT: {
      LIST: "setUserApt",
      SELECT: "setSelectUserApt",
    },
  },

  SET_PROCEDURE_APPROVAL: {
    LIST: "setProcedureApprovalsList",
    DATA: "setProcedureApproval",
  },
  SET_MONTH_AVAILABILITIES : 'setMonthAvailabilities',

};

export { AppointmentActions, AppointmentMutations };
