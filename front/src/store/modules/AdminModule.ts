import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface IAdmin {
  id: number;
  first_name: string;
  last_name: string;
  username: string;
  password: string;
  email: string;
}

export interface AdminInfo {
  adminData: Array<IAdmin>;
  adminSelectData: IAdmin;
}

@Module
export default class AdminModule extends VuexModule implements AdminInfo {
  adminData = [] as Array<IAdmin>;
  adminSelectData = {} as IAdmin;

  /**
   * Get current user object
   * @returns AdminList
   */
  get adminList(): Array<IAdmin> {
    return this.adminData;
  }

  /**
   * Get current user object
   * @returns SelectedAdminData
   */
  get getAdminSelected(): IAdmin {
    return this.adminSelectData;
  }

  @Mutation
  [Mutations.SET_ADMIN.LIST](adminData) {
    this.adminData = adminData;
  }

  @Mutation
  [Mutations.SET_ADMIN.SELECT](data) {
    this.adminSelectData = data;
  }

  @Action
  [Actions.ADMIN.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("admins")
        .then(({ data }) => {
          this.context.commit(Mutations.SET_ADMIN.LIST, data.data);
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
  [Actions.ADMIN.CREATE](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("admins", payload)
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
  [Actions.ADMIN.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("admins", item.id, item)
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
  [Actions.ADMIN.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("admins/" + id)
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
