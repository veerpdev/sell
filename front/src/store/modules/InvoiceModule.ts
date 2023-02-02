import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, VuexModule } from "vuex-module-decorators";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";

@Module
export default class InvoiceModule extends VuexModule {
  @Action
  [Actions.INVOICE.SEND](appointmentId) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post(`appointments/${appointmentId}/invoice/send`, {})
        .then(({ data }) => {
          return displaySuccessToast("Invoice sent!");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Sending invoice");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.INVOICE.VIEW](appointmentId) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post(
        `appointments/${appointmentId}/invoice`,
        {},
        {
          responseType: "blob",
        }
      )
        .then(({ data }) => {
          return data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Viewing invoice");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
