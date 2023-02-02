import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { displayServerError, displaySuccessToast } from "@/helpers/helpers";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import store from "..";
import IDocumentSection from "../interfaces/IDocumentTemplate";

export interface DocumentTemplateInfo {
  documentTemplatesData: Array<IDocumentSection>;
  documentAppointmentsData: Array<unknown>;
  documentTemplateSelectData: IDocumentSection;
  documentAppointmentSelectData: unknown;
  documentHeaderFooterSelectData: unknown;
}

@Module
export default class DocumentTemplateModule
  extends VuexModule
  implements DocumentTemplateInfo
{
  documentTemplatesData = [] as Array<IDocumentSection>;
  documentTemplateSelectData = {} as IDocumentSection;

  documentAppointmentsData = [] as Array<unknown>;
  documentAppointmentSelectData = {} as unknown;
  documentHeaderFooterSelectData = {} as unknown;

  /**
   * Get Document Template for current organization
   * @returns documentTemplatesData
   */
  get getDocumentTemplateList(): Array<IDocumentSection> {
    return this.documentTemplatesData;
  }

  /**
   * Get Selected Document Template for current organization
   * @returns documentTemplateSelectData
   */
  get getDocumentTemplateSelected(): IDocumentSection {
    return this.documentTemplateSelectData;
  }

  get getDocumentAppointmentSelected(): unknown {
    return this.documentAppointmentSelectData;
  }

  get getDocumentHeaderFooterSelected(): unknown {
    return this.documentHeaderFooterSelectData;
  }

  @Mutation
  [Mutations.SET_DOCUMENT_APPOINTMENTS.LIST](data) {
    this.documentAppointmentsData = data;
  }

  @Mutation
  [Mutations.SET_DOCUMENT_TEMPLATES.LIST](data) {
    this.documentTemplatesData = data;
  }

  @Mutation
  [Mutations.SET_DOCUMENT_TEMPLATES.SELECT](data) {
    this.documentTemplateSelectData = data.template;
    this.documentAppointmentSelectData = data.appointment;
    this.documentHeaderFooterSelectData = data.headerFooter;
  }

  @Action
  [Actions.DOCUMENT_TEMPLATES.LIST](documentType = null) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      let params = {};

      if (documentType) {
        params = {
          type: documentType,
        };
      }

      return ApiService.query("document-templates", { params: params })
        .then(({ data }) => {
          this.context.commit(Mutations.SET_DOCUMENT_TEMPLATES.LIST, data.data);
          return Promise.resolve();
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Getting document templates list"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.DOCUMENT_TEMPLATES.CREATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("document-templates", item)
        .then(({ data }) => {
          store.dispatch(Actions.DOCUMENT_TEMPLATES.LIST);
          return displaySuccessToast("Document template created successfully");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Creating document template");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.DOCUMENT_TEMPLATES.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.update("document-templates", item.id, item)
        .then(({ data }) => {
          store.dispatch(Actions.DOCUMENT_TEMPLATES.LIST);
          return displaySuccessToast("Document template updated successfully");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating document template");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.DOCUMENT_TEMPLATES.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.delete("document-templates/" + id)
        .then((data) => {
          store.dispatch(Actions.DOCUMENT_TEMPLATES.LIST);
          return displaySuccessToast("Document template deleted successfully");
        })
        .catch((response) => {
          return displayServerError(response, "Deleting document template");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
