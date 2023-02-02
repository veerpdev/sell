import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface IHeaderFooterTemplate {
  id: string;
}

export interface IHeaderFooterInfo {
  headerFooterTemplateData: Array<IHeaderFooterTemplate>;
  headerFooterTemplateSelectData: IHeaderFooterTemplate;
}

@Module
export default class HeaderFooterModule
  extends VuexModule
  implements IHeaderFooterInfo
{
  headerFooterTemplateData = [] as Array<IHeaderFooterTemplate>;
  headerFooterTemplateSelectData = {} as IHeaderFooterTemplate;

  /**
   * Get current header/footer templates List
   * @returns Patients
   */
  get getHeaderFooterTemplateList(): Array<IHeaderFooterTemplate> {
    return this.headerFooterTemplateData;
  }

  /**
   * Get current selected header/footer template
   * @returns Patients
   */
  get getHeaderFooterTemplateSelect(): IHeaderFooterTemplate {
    return this.headerFooterTemplateSelectData;
  }

  @Mutation
  [Mutations.SET_HEADER_FOOTER_TEMPLATE.LIST](templatesData) {
    this.headerFooterTemplateData = templatesData;
  }

  @Mutation
  [Mutations.SET_HEADER_FOOTER_TEMPLATE.SELECT](templateData) {
    this.headerFooterTemplateSelectData = templateData;
  }

  @Action
  [Actions.HEADER_FOOTER_TEMPLATE.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("document-header-footer-templates")
        .then(({ data }) => {
          this.context.commit(
            Mutations.SET_HEADER_FOOTER_TEMPLATE.LIST,
            data.data
          );
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
  [Actions.HEADER_FOOTER_TEMPLATE.CREATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("document-header-footer-templates", item.data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
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
  [Actions.HEADER_FOOTER_TEMPLATE.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("document-header-footer-templates/" + id)
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
