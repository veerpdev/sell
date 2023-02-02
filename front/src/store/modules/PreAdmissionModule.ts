import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";
import IPreAdmissionConsent from "../interfaces/IPreAdmissionConsent";

export interface PreAdmissionInfo {
  consent: IPreAdmissionConsent;
}

@Module
export default class PreAdmissionModule
  extends VuexModule
  implements PreAdmissionInfo
{
  preAdmissionConsent = {} as IPreAdmissionConsent;

  /**
   * Get preadmission consent object
   * @returns consent
   */
  get consent(): IPreAdmissionConsent {
    return this.preAdmissionConsent;
  }

  @Mutation
  [Mutations.SET_PREADMISSION.CONSENT](data) {
    this.preAdmissionConsent = data;
  }

  @Action
  [Actions.PREADMISSION.CONSENT.VIEW]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("get-pre-admission-consent")
        .then(({ data }) => {
          this.context.commit(Mutations.SET_PREADMISSION.CONSENT, data.data);
        })
        .catch(({ response }) => {
          return displayServerError(response, "viewing preadmission consent");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.PREADMISSION.CONSENT.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("update-pre-admission-consent", item)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating preadmission consent");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
