import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import IScheduleFee from "../interfaces/IScheduleFee";

export interface ScheduleFeeInfo {
  scheduleFeeData: Array<IScheduleFee>;
}

@Module
export default class ScheduleFeeModule
  extends VuexModule
  implements ScheduleFeeInfo
{
  scheduleFeeData = [] as Array<IScheduleFee>;

  @Action
  [Actions.SCHEDULE_FEE.CREATE](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("schedule-fees", payload)
        .then(({ data }) => {
          return Promise.resolve();
        })
        .catch(({ response }) => {
          let message = "Could not create schedule fee.";
          if (response?.data?.message) {
            message = response.data.message;
          }

          return Promise.reject(message);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.SCHEDULE_FEE.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("schedule-fees", item.id, item)
        .then(({ data }) => {
          return Promise.resolve();
        })
        .catch(({ response }) => {
          let message = "Could not update schedule fee.";
          if (response?.data?.message) {
            message = response.data.message;
          }

          return Promise.reject(message);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.SCHEDULE_FEE.DELETE](id) {
    console.log(id);
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("schedule-fees/" + id)
        .then(({ data }) => {
          return Promise.resolve();
        })
        .catch(({ response }) => {
          let message = "Could not delete schedule fee.";
          if (response?.data?.message) {
            message = response.data.message;
          }

          return Promise.reject(message);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
