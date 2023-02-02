import BillingApiService from "@/core/services/BillingApiService";
import { Mutations } from "@/store/enums/StoreEnums";
import {
  PatientActions,
  PatientMutations,
} from "@/store/enums/StorePatientEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface ValidationDataResponse {
  verified: boolean;
  update_details: Array<string>;
  message: string;
  errors: Array<string>;
}

export interface ValidationResponse {
  success: boolean;
  data: ValidationDataResponse;
}

export interface ValidationInfo {
  errors: unknown;
  sourceValidationResponse: ValidationResponse;
  isVerified: boolean;
}

@Module
export default class BillingValidationModule
  extends VuexModule
  implements ValidationInfo
{
  errors = {};
  sourceValidationResponse = {} as ValidationResponse;
  isVerified = false;

  /**
   * Get medicare validation response
   * @returns ValidationResponse
   */
  get validationResponse(): ValidationResponse {
    return this.sourceValidationResponse;
  }

  @Mutation
  [PatientMutations.CLAIM_SOURCE.SET_VALIDATION](data) {
    this.sourceValidationResponse = data;
  }

  @Mutation
  [Mutations.SET_ERROR](error) {
    this.errors = { ...error, status: "failed" };
  }

  @Action
  async [PatientActions.CLAIM_SOURCE.VALIDATE_MEDICARE](details) {
    if (await BillingApiService.setHeader()) {
      return BillingApiService.post("api/validate/medicare", details)
        .then(({ data }) => {
          if (data.success) {
            this.context.commit(
              PatientMutations.CLAIM_SOURCE.SET_VALIDATION,
              data
            );
          } else {
            throw data;
          }
        })
        .catch(({ response }) => {
          this.context.commit(Mutations.SET_ERROR, response.data.data);
          return Promise.reject();
        });
    }
  }

  @Action
  async [PatientActions.CLAIM_SOURCE.VALIDATE_CONCESSION](details) {
    if (await BillingApiService.setHeader()) {
      return BillingApiService.post("api/validate/concession", details)
        .then(({ data }) => {
          if (data.success) {
            this.context.commit(
              PatientMutations.CLAIM_SOURCE.SET_VALIDATION,
              data
            );
          } else {
            throw data;
          }
        })
        .catch(({ response }) => {
          this.context.commit(Mutations.SET_ERROR, response.data.data);
          return Promise.reject();
        });
    }
  }

  @Action
  async [PatientActions.CLAIM_SOURCE.VALIDATE_HEALTH_FUND](details) {
    if (await BillingApiService.setHeader()) {
      return BillingApiService.post("api/validate/healthfund", details)
        .then(({ data }) => {
          if (data.success) {
            this.context.commit(
              PatientMutations.CLAIM_SOURCE.SET_VALIDATION,
              data
            );
          } else {
            throw data;
          }
        })
        .catch(({ response }) => {
          this.context.commit(Mutations.SET_ERROR, response.data.data);
          return Promise.reject();
        });
    }
  }

  @Action
  async [PatientActions.CLAIM_SOURCE.VALIDATE_DVA](details) {
    if (await BillingApiService.setHeader()) {
      return BillingApiService.post("api/validate/dva", details)
        .then(({ data }) => {
          if (data.success) {
            this.context.commit(
              PatientMutations.CLAIM_SOURCE.SET_VALIDATION,
              data
            );
          } else {
            throw data;
          }
        })
        .catch(({ response }) => {
          this.context.commit(Mutations.SET_ERROR, response.data.data);
          return Promise.reject();
        });
    }
  }
}
