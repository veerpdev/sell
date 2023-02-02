/* eslint-disable prettier/prettier */
const Actions = {
  // action types
  ADD_BODY_CLASSNAME: "addBodyClassName",
  REMOVE_BODY_CLASSNAME: "removeBodyClassName",
  ADD_BODY_ATTRIBUTE: "addBodyAttribute",
  REMOVE_BODY_ATTRIBUTE: "removeBodyAttribute",
  ADD_CLASSNAME: "addClassName",
  VERIFY_AUTH: "verifyAuth",
  LOGIN: "login",
  LOGOUT: "logout",
  RESEND_TWO_FACTOR_CODE: "resendTwoFactorCode",
  REGISTER: "register",
  UPDATE_USER: "updateUser",
  FORGOT_PASSWORD: "forgotPassword",
  SET_BREADCRUMB_ACTION: "setBreadcrumbAction",
  USER_LIST: "userList",
  BILLING_TOKEN: "getBillingToken",

  FILE: {
    VIEW: "fileView",
  },

  PROFILE: {
    VIEW: "viewProfile",
    UPDATE: "updateProfile",
    UPDATE_PASSWORD: "updatePassword",
    UPDATE_SIGNATURE: "updateSignature",
    PIN: {
      SET: "setUserPin",
      VERIFY: "verifyUserPin",
      SHOW: "showUserPin",
    },
  },

  ADMIN: {
    LIST: "listAdmin",
    CREATE: "createAdmin",
    DELETE: "deleteAdmin",
    UPDATE: "updateAdmin",
  },

  ORG: {
    LIST: "listOrg",
    CREATE: "createOrg",
    DELETE: "deleteOrg",
    UPDATE: "updateOrg",
    SELECT: "selectOrg",
  },

  ORG_ADMIN: {
    UPLOAD_IMAGE: "uploadImage",
    LOAD_ORGANIZATION_DATA: "loadOrganizationData",
    ORGANIZATION: {
      SETTINGS: {
        UPDATE: "updateOrganizationSettings",
      },
      PRE_ADMISSION_SECTION: {
        LIST: "listPreAdmissionSections",
        UPDATE: "updatePreAdmissionSections",
      },
    },
  },

  CLINICS: {
    LIST: "listClinics",
    CREATE: "createClinics",
    DELETE: "deleteClinics",
    UPDATE: "updateClinics",
    ROOMS: {
      LIST: "listClinicsRooms",
      CREATE: "createClinicsRooms",
      DELETE: "deleteClinicsRooms",
      UPDATE: "updateClinicsRooms",
    },
    MINOR_ID: "createClinicMinorId",
  },

  HEALTH_FUND: {
    LIST: "listHealthFunds",
  },

  SPECIALIST: {
    LIST: "listSpecialists",
    CREATE: "createSpecialists",
    DELETE: "deleteSpecialists",
    UPDATE: "updateSpecialists",
    SEARCH: {
      LIST: "listSearchSpecialist",
    },
  },

  EMPLOYEE: {
    LIST: "listEmployee",
    CREATE: "createEmployee",
    DELETE: "deleteEmployee",
    UPDATE: "updateEmployee",
    UPDATE_PASSWORD: "updateEmployeePassword",
  },

  ANESTHETIST_QUES: {
    LIST: "listAnesthetistQues",
    CREATE: "createAnesthetistQues",
    DELETE: "deleteAnesthetistQues",
    UPDATE: "updateAnesthetistQues",
    ACTIVE_LIST: "listActiveAnesthetistQues",
  },

  NTF_TEMPLATES: {
    LIST: "listNtfyTemplates",
    UPDATE: "updateNtfyTemplates",
  },

  DOCUMENT_TEMPLATES: {
    LIST: "listDocumentTemplate",
    CREATE: "createDocumentTemplate",
    UPDATE: "updateDocumentTemplate",
    DELETE: "deleteDocumentTemplate",
  },

  MAILS: {
    LIST: "listMail",
    VIEW: "viewMail",
    COMPOSE: "composeMail",
    SEND: "sendMail",
    SEND_DRAFT: "mailSendDraft",
    STAR: "starMail",
    UN_STAR: "unStarMail",
    DELETE: "deleteMail",
    RESTORE: "restoreMail",
    DELETE_DRAFT: "deleteMailDraft",
    UPDATE_DRAFT: "updateMailDraft",
  },

  APT_TIME_REQUIREMENT: {
    LIST: "listAptTimeRequirement",
    CREATE: "createAptTimeRequirement",
    DELETE: "deleteAptTimeRequirement",
    UPDATE: "updateAptTimeRequirement",
  },

  DOCTOR_ADDRESS_BOOK: {
    LIST: "searchDoctorAddressBook", // (get) 'doctor-address-books'
    CREATE: "createDoctorAddressBook",
    DELETE: "deleteDoctorAddressBook",
    UPDATE: "updateDoctorAddressBook",
    FIND_BY_PROVIDER_NO: "findDoctorAddressByProviderNo",
  },

  LETTER: {
    CREATE: "createLetter",
  },

  MAKE_PAYMENT: {
    LIST: "listMakePayment",
    VIEW: "viewMakePayment",
    CREATE: "createMakePayment",
    INVOICE: {
      SEND: "sendInvoiceMakePayment",
      VIEW: "viewInvoiceMakePayment",
    },
  },

  HEADER_FOOTER_TEMPLATE: {
    LIST: "listHeaderFooterTemplate",
    IMAGE: "downloadHeaderFooterTemplateImage",
    CREATE: "createHeaderFooterTemplate",
    UPDATE: "updateHeaderFooterTemplate",
    DELETE: "deleteHeaderFooterTemplate",
  },

  MBS: {
    LIST: "listMbsItems",
  },

  SCHEDULE_FEE: {
    LIST: "listScheduleFee",
    CREATE: "createScheduleFee",
    DELETE: "deleteScheduleFee",
    UPDATE: "updateScheduleFee",
  },

  SCHEDULE_ITEM: {
    LIST: "listScheduleItem",
    CREATE: "createScheduleItem",
    DELETE: "deleteScheduleItem",
    UPDATE: "updateScheduleItem",
  },

  OUTGOING: {
    LIST: "getOutgoingLogs",
  },

  INVOICE: {
    SEND: "sendInvoiceWithoutPayment",
    VIEW: "viewInvoiceWithoutPayment",
  },

  AUTO_TEXT: {
    LIST: "getAutoTexts",
    CREATE: "createAutoText",
    DELETE: "deleteAutoText",
    UPDATE: "updateAutoText",
  },

  PREADMISSION: {
    CONSENT: {
      VIEW: "getPreadmissionConsent",
      UPDATE: "updatePreadmissionConsent",
    },
  },
};

const Mutations = {
  // mutation types
  SET_CLASSNAME_BY_POSITION: "appendBreadcrumb",
  PURGE_AUTH: "logOut",
  SET_AUTH: "setAuth",
  SET_USER: "setUser",
  SET_PASSWORD: "setPassword",
  SET_PROFILE: "setProfile",
  SET_ERROR: "setError",
  SET_BREADCRUMB_MUTATION: "setBreadcrumbMutation",
  SET_LAYOUT_CONFIG: "setLayoutConfig",
  RESET_LAYOUT_CONFIG: "resetLayoutConfig",
  OVERRIDE_LAYOUT_CONFIG: "overrideLayoutConfig",
  SET_USER_LIST: "setUserList",
  SET_BILLING_TOKEN: "setBillingToken",
  PURGE_BILLING_TOKEN: "deleteBillingToken",
  SET_BILLING_VALIDATION: "setBillingValidation",
  SET_AUTO_TEXTS: {
    LIST: "setAutoTexts",
    SELECT: "setSelectAutoText",
  },

  SET_SPECIALIST: {
    LIST: "setSpecialists",
    SELECT: "setSelectSpecialists",
    SEARCH: {
      SEARCH_LIST: "setSearchSpecialists",
    },
  },

  SET_ADMIN: {
    LIST: "setAdminList",
    SELECT: "setSelectAdmin",
  },

  SET_ORG: {
    LIST: "setOrgList",
    SELECT: "setSelectOrg",
  },

  SET_ORG_MANAGER: {
    LIST: "setOrgManagerList",
    SELECT: "setSelectOrgManager",
    ORGNIZATION: "settingOrganization",
  },

  SET_ORG_ADMIN: {
    LIST: "setOrgAdminList",
    SELECT: "setSelectOrgAdmin",
  },

  SET_CLINICS: {
    LIST: "setClinicsList",
    SELECT: "setSelectClinics",
    LISTROOMS: "setClinicsRoomsList",
    SELECTROOMS: "setSelectClinicsRooms",
    MINOR_ID: "setClinicMinorId",
  },

  SET_HEALTH_FUND: {
    LIST: "setHealthFundsList",
  },

  SET_EMPLOYEE: {
    LIST: "setEmployeeList",
    SELECT: "setSelectEmployee",
  },

  SET_ANESTHETIST_QUES: {
    LIST: "setAnesQuesList",
    SELECT: "setSelectAnesQuesSelect",
    ACTIVE_LIST: "setAnesQuesActiveList",
  },

  SET_DOCTOR_ADDRESS_BOOK: {
    LIST: "setDoctorAddressBook",
  },

  SET_DOCUMENT_TEMPLATES: {
    LIST: "setDocumentTemplates",
    SELECT: "setSelectDocumentTemplate",
  },

  SET_DOCUMENT_APPOINTMENTS: {
    LIST: "setDocumentAppointments",
    SELECT: "setSelectDocumentAppointment",
  },

  SET_MAILS: {
    INBOX: "setInboxMails",
    SELECT: "setSelectedMail",
  },

  SET_APT_TIME_REQUIREMENT: {
    LIST: "setAptTimeRequirement",
    SELECT: "setSelectAptTimeRequirement",
  },

  SET_NTF_TEMPLATES: {
    LIST: "setNtfTemplates",
    SELECT: "setSelectNtfTemplates",
  },

  SET_MAKE_PAYMENT: {
    LIST: "setMakePayment",
    SELECT: "setSelectMakePayment",
  },

  SET_HEADER_FOOTER_TEMPLATE: {
    LIST: "setHeaderFootersTemplate",
    SELECT: "setHeaderFooterTemplateSelect",
  },

  SET_MBS: {
    LIST: "setMbsItems",
  },

  SET_SCHEDULE_FEE: {
    LIST: "setScheduleFeeList",
    SELECT: "selectScheduleFee",
  },

  SET_SCHEDULE_ITEM: {
    LIST: "setScheduleItemList",
    SELECT: "selectScheduleItem",
  },

  SET_OUTGOING: {
    LIST: "setOutgoingLogs",
    SELECT: "setOutgoingSelectLog",
  },

  SET_PREADMISSION: {
    CONSENT: "setPreAdmissionConsent",
  },
};

export { Actions, Mutations };
