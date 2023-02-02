/* eslint-disable prettier/prettier */
const DocumentActions = {
  LIST: "documentList",
  SHOW: "documentShow",
  UPDATE: "update",
  SAVE: "saveDocument",
  PATIENT_PREVIEW: "patientPreview",
};

const DocumentMutations = {
  SET_LIST: "setDocumentList",
  SET_SELECTED_DOCUMENT: "setSelectedDocument",
};

export { DocumentActions, DocumentMutations };
