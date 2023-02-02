/* eslint-disable prettier/prettier */
const CodingActions = {
    LIST: "listAppointmentsForCoding",
    SEARCH_DIAGNOSES: "searchDiagnosisCodes",
    DOCUMENT_VIEW: "viewDocument",
    UPDATE: "updateAppointmentDetail",
    CHECK_APPOINTMENTS_COMPLETE: "CheckAppointmentsComplete",
    GENERATE_CODING_REPORT: "GetGenerateCodingReportFile"
};

const CodingMutations = {
  SET_LIST: "setListAppointmentsForCoding",
  SET_SELECT: "setSelectedAppointmentsForCoding",
};

export { CodingActions, CodingMutations };
