import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Mutations } from "@/store/enums/StoreEnums";
import {
  PatientActions,
  PatientMutations,
} from "@/store/enums/StorePatientEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface IPatientsRecall {
  id: string;
}

export interface PatientsRecallInfo {
  patientsRecallData: Array<IPatientsRecall>;
  patientsRecallSelectData: IPatientsRecall;
}

@Module
export default class PatientsRecallModule
  extends VuexModule
  implements PatientsRecallInfo
{
  patientsRecallData = [] as Array<IPatientsRecall>;
  patientsRecallSelectData = {} as IPatientsRecall;

  /**
   * Get current PatientsRecall List
   * @returns PatientsRecall
   */
  get patientsRecallList(): Array<IPatientsRecall> {
    return this.patientsRecallData;
  }

  /**
   * Get current selected Patient
   * @returns SelectedpatientsRecallData
   */
  get selectedPatientRecallList(): IPatientsRecall {
    return this.patientsRecallSelectData;
  }

  @Mutation
  [PatientMutations.SET_PATIENT_RECALL.LIST](patientsRecallData) {
    this.patientsRecallData = patientsRecallData;
  }

  @Action
  [PatientActions.RECALL.LIST](params) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.query("patients/recalls", { params: params })
        .then(({ data }) => {
          this.context.commit(
            PatientMutations.SET_PATIENT_RECALL.LIST,
            data.data
          );
          return data.data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.RECALL.CREATE](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("patients/recalls/", data)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [PatientActions.RECALL.UPDATE](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("patients/recalls/", data.id, data)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
