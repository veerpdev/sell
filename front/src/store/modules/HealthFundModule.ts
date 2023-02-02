import BillingApiService from "@/core/services/BillingApiService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface IHealthFunds {
  id: number;
  name: string;
  code: string;
}

export interface HealthFundsInfo {
  healthFundsData: Array<IHealthFunds>;
}

@Module
export default class HealthFundsModule
  extends VuexModule
  implements HealthFundsInfo
{
  healthFundsData = [] as Array<IHealthFunds>;

  /**
   * Get current user object
   * @returns AdminList
   */
  get healthFundsList(): Array<IHealthFunds> {
    return this.healthFundsData;
  }

  @Mutation
  [Mutations.SET_HEALTH_FUND.LIST](healthFundsData) {
    this.healthFundsData = healthFundsData;
  }

  @Action
  async [Actions.HEALTH_FUND.LIST]() {
    if (await BillingApiService.setHeader()) {
      return BillingApiService.get("api/health-funds")
        .then(({ data }) => {
          if (data.success) {
            this.context.commit(
              Mutations.SET_HEALTH_FUND.LIST,
              data.data.health_funds
            );
          } else {
            throw data;
          }
        })
        .catch(({ response }) => {
          console.log(response.data.error);
          // this.context.commit(Mutations.SET_ERROR, response.data.data);
          return Promise.reject();
        });
    }
  }
}
