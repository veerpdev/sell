import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import { HRMActions, HRMMutations } from "../enums/StoreHRMEnums";
import IBulletin from "../interfaces/IBulletin";

export interface BulletinInfo {
  bulletinData: Array<IBulletin>;
  bulletinSelectData: IBulletin;
}

@Module
export default class BulletinModule extends VuexModule implements BulletinInfo {
  bulletinData = [] as Array<IBulletin>;
  bulletinSelectData = {} as IBulletin;

  /**
   * Get current user object
   * @returns AdminList
   */
  get getBulletinList(): Array<IBulletin> {
    return this.bulletinData;
  }

  /**
   * Get current user object
   * @returns SelectedaptTypesData
   */
  get getBulletinSelected(): IBulletin {
    return this.bulletinSelectData;
  }

  @Mutation
  [HRMMutations.BULLETIN.SET_LIST](bulletinData) {
    this.bulletinData = bulletinData;
  }

  @Mutation
  [HRMMutations.BULLETIN.SET_SELECT](data) {
    this.bulletinSelectData = data;
  }

  @Action
  [HRMActions.BULLETIN.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("bulletins")
        .then(({ data }) => {
          this.context.commit(HRMMutations.BULLETIN.SET_LIST, data.data);
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
  [HRMActions.BULLETIN.CREATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("bulletins", item)
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
  [HRMActions.BULLETIN.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("bulletins", item.id, item)
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
  [HRMActions.BULLETIN.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("bulletins/" + id)
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
