import ApiService from "@/core/services/ApiService";
import { Actions } from "@/store/enums/StoreEnums";
import { Module, Action, VuexModule } from "vuex-module-decorators";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";

@Module
export default class FileModule extends VuexModule {
  @Action
  [Actions.FILE.VIEW](data) {
    return ApiService.post(
      "file",
      {
        path: data.path,
      },
      {
        responseType: "blob",
      }
    )
      .then(({ data }) => {
        return data;
      })
      .catch(({ response }) => {
        return displayServerError(response, "Viewing a File");
      });
  }
}
