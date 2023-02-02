import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface IAnesthetist {
  id: number;
}

export interface AnesthetistInfo {
  aneQuestionData: Array<IAnesthetist>;
  aneQuestionSelected: IAnesthetist;
  aneQuestionActiveData: Array<IAnesthetist>;
}

@Module
export default class AnesthetistModule
  extends VuexModule
  implements AnesthetistInfo
{
  aneQuestionData = [] as Array<IAnesthetist>;
  aneQuestionSelected = {} as IAnesthetist;
  aneQuestionActiveData = {} as Array<IAnesthetist>;

  /**
   * Get Active Anesthetist List
   * @returns aneQuestionData
   */
  get getAneQuestionList(): Array<IAnesthetist> {
    return this.aneQuestionData;
  }

  /**
   * Get Active Anesthetist Question List
   * @returns aneQuestionActiveData
   */
  get getAneQuestionActiveList(): Array<IAnesthetist> {
    return this.aneQuestionActiveData;
  }

  /**
   * Get current user object
   * @returns aneQuestionSelected
   */
  get getAneQuestionSelected(): IAnesthetist {
    return this.aneQuestionSelected;
  }

  @Mutation
  [Mutations.SET_ANESTHETIST_QUES.LIST](aneQuestionData) {
    this.aneQuestionData = aneQuestionData;
  }

  @Mutation
  [Mutations.SET_ANESTHETIST_QUES.ACTIVE_LIST](aneQuestionActiveData) {
    this.aneQuestionActiveData = aneQuestionActiveData;
  }

  @Mutation
  [Mutations.SET_ANESTHETIST_QUES.SELECT](data) {
    this.aneQuestionSelected = data;
  }

  @Action
  [Actions.ANESTHETIST_QUES.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("anesthetic-questions")
        .then(({ data }) => {
          this.context.commit(Mutations.SET_ANESTHETIST_QUES.LIST, data.data);
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
  [Actions.ANESTHETIST_QUES.ACTIVE_LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("anesthetic-questions")
        .then(({ data }) => {
          this.context.commit(
            Mutations.SET_ANESTHETIST_QUES.ACTIVE_LIST,
            data.data
          );
          return data.data;
        })
        .catch(() => {
          // this.context.commit(Mutations.SET_ERROR, response.data.errors);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.ANESTHETIST_QUES.CREATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("anesthetic-questions", item)
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
  [Actions.ANESTHETIST_QUES.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("anesthetic-questions", item.id, item)
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
  [Actions.ANESTHETIST_QUES.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("anesthetic-questions/" + id)
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
