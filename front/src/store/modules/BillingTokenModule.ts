import BillingApiService from "@/core/services/BillingApiService";
import BillingTokenService from "@/core/services/BillingTokenService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface Token {
  token_type: string;
  expires_in: number;
  access_token: string;
}

export interface BillingAuthInfo {
  errors: unknown;
  token: Token;
}

@Module
export default class BillingTokenModule
  extends VuexModule
  implements BillingAuthInfo
{
  errors = {};
  token = {} as Token;

  /**
   * Get current token object
   * @returns Token
   */
  get getToken(): Token {
    return this.token;
  }

  @Mutation
  [Mutations.SET_ERROR](error) {
    this.errors = { ...error, status: "failed" };
  }

  @Mutation
  [Mutations.SET_BILLING_TOKEN](token) {
    this.token = token;
    this.errors = {};
    BillingTokenService.saveToken(token.access_token);
  }

  @Mutation
  [Mutations.PURGE_BILLING_TOKEN]() {
    this.token = {} as Token;
    this.errors = [];
    BillingTokenService.destroyToken();
  }

  @Action
  [Actions.BILLING_TOKEN]() {
    return BillingApiService.post("oauth/token", {
      grant_type: "client_credentials",
      client_id: BillingApiService.clientId,
      client_secret: BillingApiService.clientSecret,
    })
      .then(({ data }) => {
        this.context.commit(Mutations.SET_BILLING_TOKEN, data);
      })
      .catch(({ response }) => {
        this.context.commit(Mutations.SET_ERROR, response.data);
      });
  }
}
