import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import {
  AppointmentActions,
  AppointmentMutations,
} from "../enums/StoreAppointmentEnums";

export interface IProcedureApproval {
  id: string;
}

export interface ProcedureApprovalsInfo {
  procedureApproval: IProcedureApproval;
}

@Module
export default class ProcedureApprovalsModule
  extends VuexModule
  implements ProcedureApprovalsInfo
{
  procedureApproval = {} as IProcedureApproval;

  /**
   * Get current ProcedureApprovals List
   * @returns ProcedureApprovals
   */
  get getProcedureApproval(): IProcedureApproval {
    return this.procedureApproval;
  }

  @Mutation
  [AppointmentMutations.SET_PROCEDURE_APPROVAL.DATA](procedureApproval) {
    this.procedureApproval = procedureApproval;
  }

  @Action
  [AppointmentActions.PROCEDURE_APPROVAL.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.put(
        "appointment/procedure-approvals/" + item.appointment_id,
        item
      )
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
          // this.context.commit(Mutations.SET_ERROR, response.data.errors);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [AppointmentActions.PROCEDURE_APPROVAL.UPLOAD](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post(
        "/appointments/pre-admissions/upload/" + item.appointment_id,
        item.uploadData
      )
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
          // this.context.commit(Mutations.SET_ERROR, response.data.errors);
          return response;
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
