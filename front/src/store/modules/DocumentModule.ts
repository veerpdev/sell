import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Mutations } from "@/store/enums/StoreEnums";
import {
  DocumentActions,
  DocumentMutations,
} from "@/store/enums/StoreDocumentEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import { displayServerError } from "@/helpers/helpers";

export interface IDocument {
  id: number;
}

export interface Documents {
  documentsData: Array<IDocument>;
  selectedDocument: unknown;
}

@Module
export default class DocumentModule extends VuexModule implements Documents {
  documentsData = [] as Array<IDocument>;
  selectedDocument = null;

  get documentsList(): Array<IDocument> {
    return this.documentsData;
  }

  get getSelectedDocument(): unknown {
    return this.selectedDocument;
  }

  @Mutation
  [DocumentMutations.SET_LIST](data) {
    this.documentsData = data;
  }

  @Mutation
  [DocumentMutations.SET_SELECTED_DOCUMENT](data = null) {
    this.selectedDocument = data;
  }

  @Action
  [DocumentActions.LIST](data) {
    if (JwtService.getToken()) {
      this.context.commit(DocumentMutations.SET_LIST, []);
      ApiService.setHeader();
      return ApiService.query("documents", {
        params: {
          specialist_id: data.specialist_id,
          appointment_id: data.appointment_id,
          patient_id: data.patient_id,

          before_date: data.before_date,
          after_date: data.after_date,

          is_seen: data.is_seen,
          origin: data.origin,

          is_missing_information: data.is_missing_information,
        },
      })
        .then(({ data }) => {
          this.context.commit(DocumentMutations.SET_LIST, data.data);
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
  [DocumentActions.UPDATE](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.put("documents/" + data.document_id, data)
        .then(({ data }) => {
          return data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [DocumentActions.SAVE](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("patients/documents/save/" + data.patient_id, data)
        .then(({ data }) => {
          return data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [DocumentActions.PATIENT_PREVIEW](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post(
        "patients/documents/preview/" + data.patient_id,
        data
      )
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          displayServerError(response, "previewing a patient document");
          return Promise.reject();
        });
    }
  }
}
