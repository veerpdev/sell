import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";
import INotificationTemplate from "../interfaces/INotificationTemplate";

@Module
export default class NotificationTemplatesModule extends VuexModule {
  notificationTemplatesData = [] as Array<INotificationTemplate>;
  notificationTemplateSelected = {} as INotificationTemplate;

  /**
   * Get list of all organization notification templates
   * @returns ntfTemplatesData
   */
  get getNotificationTemplatesList(): Array<INotificationTemplate> {
    return this.notificationTemplatesData;
  }

  /**
   * Get the selected notification template
   * @returns notificationTemplatesSelected
   */
  get getNotificationTemplateSelected(): INotificationTemplate {
    return this.notificationTemplateSelected;
  }

  @Mutation
  [Mutations.SET_NTF_TEMPLATES.LIST](notificationTemplatesData) {
    this.notificationTemplatesData = notificationTemplatesData;
  }

  @Mutation
  [Mutations.SET_NTF_TEMPLATES.SELECT](data) {
    this.notificationTemplateSelected = data;
  }

  @Action
  [Actions.NTF_TEMPLATES.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("notification-templates")
        .then(({ data }) => {
          this.context.commit(Mutations.SET_NTF_TEMPLATES.LIST, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing notification templates");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.NTF_TEMPLATES.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("notification-templates", item.id, item)
        .then(() => {
          return displaySuccessToast("Notification template updated");
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Updating a notification template"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
