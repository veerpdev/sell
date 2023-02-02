import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";
import IAutoText from "../interfaces/IAutoText";

export interface AutoTextInfo {
  autoTextData: Array<IAutoText>;
  autoTextSelectData: IAutoText;
}

@Module
export default class AutoTextModule extends VuexModule implements AutoTextInfo {
  autoTextData = [] as Array<IAutoText>;
  autoTextSelectData = {} as IAutoText;

  /**
   * Get all auto texts list
   * @returns AutoTextsList
   */
  get autoTextsList(): Array<IAutoText> {
    return this.autoTextData;
  }

  get autoTextSelected(): IAutoText {
    return this.autoTextSelectData;
  }

  @Mutation
  [Mutations.SET_AUTO_TEXTS.LIST](autoTextData) {
    this.autoTextData = autoTextData;
  }

  @Mutation
  [Mutations.SET_AUTO_TEXTS.SELECT](data) {
    this.autoTextSelectData = data;
  }

  @Action
  [Actions.AUTO_TEXT.LIST](params) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.query("auto-texts", { params: params })
        .then(({ data }) => {
          this.context.commit(Mutations.SET_AUTO_TEXTS.LIST, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing all auto texts");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.AUTO_TEXT.CREATE](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("auto-texts", payload)
        .then(({ data }) => {
          if (data.data) {
            displaySuccessToast("Auto text created");
          } else {
            displayServerError(data.message, "Creating a auto text");
          }
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Creating a auto text");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.AUTO_TEXT.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.update("auto-texts", item.id, item)
        .then(() => {
          return displaySuccessToast("Auto text Updated");
        })
        .catch(({ response }) => {
          displayServerError(response, "Updating a auto text");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.AUTO_TEXT.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("auto-texts/" + id)
        .then(() => {
          return displaySuccessToast("Auto text deleted");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Deleting a auto text");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
