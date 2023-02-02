import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import IUserAuth from "../interfaces/IUserAuth";

export interface UserAuthInfo {
  errors: unknown;
  user: IUserAuth;
  isAuthenticated: boolean;
}

@Module
export default class AuthModule extends VuexModule implements UserAuthInfo {
  errors = {};
  user = {} as IUserAuth;
  isAuthenticated = !!JwtService.getToken();

  /**
   * Get current user object
   * @returns User
   */
  get currentUser(): IUserAuth {
    return this.user;
  }

  /**
   * Get current user object
   * @returns User
   */
  get userRole() {
    return this.user.role;
  }
  /**
   * Get current user organization
   * @returns User
   */
  get userOrganization() {
    return this.user.organization;
  }

  /**
   * Verify user authentication
   * @returns boolean
   */
  get isUserAuthenticated(): boolean {
    return this.isAuthenticated;
  }

  /**
   * Get authentification errors
   * @returns array
   */
  get getErrors() {
    return this.errors;
  }

  @Mutation
  [Mutations.SET_ERROR](error) {
    this.errors = { ...error, status: "failed" };
  }

  @Mutation
  [Mutations.SET_AUTH](user) {
    this.isAuthenticated = true;
    this.user = user;
    this.errors = {};
    JwtService.saveToken(user.access_token);
  }

  @Mutation
  [Mutations.SET_USER](user) {
    this.user = user;
  }

  @Mutation
  [Mutations.PURGE_AUTH]() {
    this.isAuthenticated = false;
    this.user = {} as IUserAuth;
    this.errors = [];
    JwtService.destroyToken();
  }

  @Action
  [Actions.LOGIN](credentials) {
    return ApiService.post("login", credentials)
      .then(({ data }) => {
        this.context.commit(Mutations.SET_AUTH, data);
        return data;
      })
      .catch(({ response }) => {
        this.context.commit(Mutations.SET_ERROR, response.data);
        return Promise.reject();
      });
  }

  @Action
  [Actions.RESEND_TWO_FACTOR_CODE](credentials) {
    return ApiService.post("two-factor/resend", credentials)
      .then(({ data }) => {
        return Promise.resolve();
      })
      .catch(({ response }) => {
        this.context.commit(Mutations.SET_ERROR, response.data);
        return Promise.reject();
      });
  }

  @Action
  [Actions.LOGOUT]() {
    this.context.commit(Mutations.PURGE_AUTH);
  }

  @Action
  [Actions.VERIFY_AUTH](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("verify_token", payload)
        .then(({ data }) => {
          this.context.commit(Mutations.SET_AUTH, data);
        })
        .catch(({ response }) => {
          this.context.commit(Mutations.SET_ERROR, response.data);
          this.context.commit(Mutations.PURGE_AUTH);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
