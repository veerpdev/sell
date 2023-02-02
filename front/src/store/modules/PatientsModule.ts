import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import {
  PatientActions,
  PatientMutations,
} from "@/store/enums/StorePatientEnums";
import { Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import IAppointment from "./AppointmentModule";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";
import IPatient from "../interfaces/IPatient";

export interface IMinorId {
  minorId?: string | null;
}

export interface PatientsInfo {
  patientsData: Array<IPatient>;
  patientsSelectData: IPatient;
  patientDocumentList: IPatient;
}

export interface PatientAppointmentsData {
  pastAppointments: Array<IAppointment>;
  futureAppointments: Array<IAppointment>;
}

@Module
export default class PatientsModule extends VuexModule implements PatientsInfo {
  patientsData = [] as Array<IPatient>;
  patientsSelectData = {} as IPatient;
  patientDocumentList = {} as IPatient;
  patientAppointmentsData = {
    pastAppointments: [],
    futureAppointments: [],
  } as PatientAppointmentsData;
  /**
   * Get current Patients List
   * @returns Patients
   */
  get patientsList(): Array<IPatient> {
    return this.patientsData;
  }

  /**
   * Get current Patients List
   * @returns Patients
   */
  get getPatientDocumentList(): IPatient {
    return this.patientDocumentList;
  }

  /**
   * Get current selected Patient
   * @returns SelectedpatientsData
   */
  get selectedPatient(): IPatient {
    return this.patientsSelectData;
  }

  /**
   * Get current selected Patient
   * @returns SelectedpatientsData
   */
  get latestMinorId(): IMinorId {
    if (
      this.selectedPatient?.appointments &&
      this.selectedPatient.appointments.length > 0
    ) {
      const latestApt = this.selectedPatient.appointments[0];
      return {
        minorId: latestApt?.clinic?.minor_id ?? null,
      };
    }

    return {
      minorId: null,
    };
  }

  get getPatientAppointments(): PatientAppointmentsData {
    return this.patientAppointmentsData;
  }

  @Mutation
  [PatientMutations.SET_PATIENT.LIST](patientsData) {
    this.patientsData = patientsData;
  }

  @Mutation
  [PatientMutations.SET_PATIENT.DOCUMENTS.LIST](documentList) {
    this.patientDocumentList = documentList;
  }

  @Mutation
  [PatientMutations.SET_PATIENT.SELECT](data) {
    this.patientsSelectData = data;
  }

  @Mutation
  [PatientMutations.SET_PATIENT.APPOINTMENTS](data) {
    this.patientAppointmentsData = data;
  }

  @Action
  [PatientActions.LIST](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.get("patients", "", data)
        .then(({ data }) => {
          this.context.commit(PatientMutations.SET_PATIENT.LIST, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing patients");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.CREATE](patient) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("patients", patient)
        .then(({ data }) => {
          displaySuccessToast("Patient created successfully");
          return Promise.resolve();
        })
        .catch(({ response }) => {
          displayServerError(response, "Creating a patient");
          return Promise.reject();
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.UPDATE](patient) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("patients", patient.id, patient)
        .then(({ data }) => {
          this.context.dispatch(PatientActions.VIEW, patient.id);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating a patient");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.VIEW](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("patients/" + id)
        .then(({ data }) => {
          this.context.commit(PatientMutations.SET_PATIENT.SELECT, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Viewing a patient");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.APPOINTMENTS](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("patients/appointments/" + id)
        .then(({ data }) => {
          this.context.commit(
            PatientMutations.SET_PATIENT.APPOINTMENTS,
            data.data
          );
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing patient appointments");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.DOCUMENTS.LIST](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("patients/documents/" + id)
        .then(({ data }) => {
          this.context.commit(
            PatientMutations.SET_PATIENT.DOCUMENTS.LIST,
            data.data
          );
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing patient document's");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.DOCUMENTS.CREATE](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post(
        "patients/documents/" + data.get("patient_id"),
        data
      )
        .then(({ data }) => {
          displaySuccessToast("Document uploaded");
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Creating a patient document");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.DOCUMENTS.SEND_VIA_EMAIL](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("patients/documents/send/email", data)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          displayServerError(response, "Sending a document via email");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.DOCUMENTS.SEND_VIA_HEALTH_LINK](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("patients/documents/send/health-link", data)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Sending a patient document via health link"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.CLAIM_SOURCE.ADD](source) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.put("patients/billing", source)
        .then(() => {
          this.context.dispatch(PatientActions.VIEW, source.patient_id);
          return displaySuccessToast("Claim source added successfully");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Add a patient claim source");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.CLAIM_SOURCE.UPDATE](source) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.update("patients/billing", source.id, source)
        .then(({ data }) => {
          this.context.dispatch(PatientActions.VIEW, source.patient_id);
          return displaySuccessToast("Claim source updated successfully");
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Updating a patient claim source"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.CLAIM_SOURCE.DELETE](source) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.delete(`patients/billing/${source.id}`)
        .then(() => {
          this.context.dispatch(PatientActions.VIEW, source.patient_id);

          return displaySuccessToast("Successfully Deleted claim source");
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Deleting a patient claim source"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.ALLERGY.ADD](allergy) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post(`patients/allergies`, allergy)
        .then(() => {
          this.context.dispatch(PatientActions.VIEW, allergy.patient_id);

          return displaySuccessToast("Patient allergy added successfully");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Adding a patient allergy");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.ALLERGY.UPDATE](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.update(`patients/allergies`, data.id, data)
        .then(() => {
          this.context.dispatch(PatientActions.VIEW, data.patient_id);

          return displaySuccessToast("Patient allergy updated successfully");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating a patient allergy");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.ALLERGY.DELETE](allergy) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.delete(`patients/allergies/${allergy.id}`)
        .then(() => {
          this.context.dispatch(PatientActions.VIEW, allergy.patient_id);

          return displaySuccessToast("Successfully deleted allergy");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Deleting a patient allergy");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.MEDICATION.ADD](medication) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post(`patients/medications`, medication)
        .then(() => {
          this.context.dispatch(PatientActions.VIEW, medication.patient_id);

          return displaySuccessToast("Patient medication added successfully");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Adding a patient medication");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.MEDICATION.UPDATE](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.update(`patients/medications`, data.id, data)
        .then(() => {
          this.context.dispatch(PatientActions.VIEW, data.patient_id);

          return displaySuccessToast("Patient medication updated successfully");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating a patient medication");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.MEDICATION.DELETE](medication) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.delete(`patients/medications/${medication.id}`)
        .then(() => {
          this.context.dispatch(PatientActions.VIEW, medication.patient_id);

          return displaySuccessToast("Successfully deleted medication");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Deleting a patient medication");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.ALSO_KNOWN_AS.CREATE](details) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.put("patients/also-known-as", details)
        .then(({ data }) => {
          this.context.dispatch(PatientActions.VIEW, details.patient_id);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Creating a patient 'also know as'"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.ALSO_KNOWN_AS.UPDATE](details) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("patients/also-known-as", details.id, details)
        .then(({ data }) => {
          this.context.dispatch(PatientActions.VIEW, details.patient_id);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Updating a patient 'also know as'"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.ALSO_KNOWN_AS.BULK](details) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("patients/also-known-as/bulk", details.id, details.data)
        .then(({ data }) => {
          this.context.dispatch(PatientActions.VIEW, details.patient_id);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing patient 'also know as'");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.ALSO_KNOWN_AS.DELETE](details) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete(`patients/also-known-as/${details.id}`)
        .then(() => {
          this.context.dispatch(PatientActions.VIEW, details.patient_id);
          return displaySuccessToast("Successfully updated 'also know as'");
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Deleting a patient 'also know as'"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.PRINT_LABEL](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post(
        "patients/print-label/" + id,
        {},
        {
          responseType: "blob",
        }
      )
        .then(({ data }) => {
          return data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
