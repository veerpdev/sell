import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { displayServerError, displaySuccessToast } from "@/helpers/helpers";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface IUser {
  id: number;
  first_name: string;
  last_name: string;
  username: string;
  email: string;
  photo: string;
  role: Array<unknown>;
  role_id: number;
}

export interface UserInfo {
  users: Array<IUser>;
}

@Module
export default class UserModule extends VuexModule implements UserInfo {
  users = [] as Array<IUser>;

  /**
   * Get User List in Organisation
   * @returns users
   */
  get getUserList(): Array<IUser> {
    return this.users;
  }

  @Mutation
  [Mutations.SET_USER_LIST](users) {
    this.users = users;
  }

  @Action
  [Actions.USER_LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("users")
        .then(({ data }) => {
          this.context.commit(Mutations.SET_USER_LIST, data.data);
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
  [Actions.PROFILE.PIN.SET](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.put("pin/set", data)
        .then(({ data }) => {
          return displaySuccessToast("Pin updated successfully");
        })
        .catch(({ response }) => {
          return displayServerError(response, "updating a pin");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.PROFILE.PIN.VERIFY](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("pin/verify", data)
        .then(({ data }) => {
          displaySuccessToast("Pin verified");
          return data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "verifying a pin");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.PROFILE.PIN.SHOW]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.get("pin/show")
        .then(({ data }) => {
          if (data.pin) {
            return Promise.resolve(data.pin);
          } else {
            throw new Error();
          }
        })
        .catch(({ response }) => {
          return displayServerError(response, "showing a pin");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
