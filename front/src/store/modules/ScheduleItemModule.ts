import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import IScheduleItem from "../interfaces/IScheduleItem";

export interface ScheduleItemInfo {
  scheduleItemData: Array<IScheduleItem>;
}

@Module
export default class ScheduleItemModule
  extends VuexModule
  implements ScheduleItemInfo
{
  scheduleItemData = [] as Array<IScheduleItem>;
  scheduleItemSelectData = {} as IScheduleItem;

  /**
   * Get Schedule Item List
   * @returns Schedule Item List
   */
  get scheduleItemList(): Array<IScheduleItem> {
    return this.scheduleItemData;
  }

  /**
   * Get current Schedule Item
   * @returns Schedule Item
   */
  get getScheduleItemSelectedList(): IScheduleItem {
    return this.scheduleItemSelectData;
  }

  @Mutation
  [Mutations.SET_SCHEDULE_ITEM.LIST](data) {
    this.scheduleItemData = data;
  }

  @Mutation
  [Mutations.SET_SCHEDULE_ITEM.SELECT](scheduleItemData) {
    this.scheduleItemSelectData = scheduleItemData;
  }

  @Action
  [Actions.SCHEDULE_ITEM.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.get("schedule-items")
        .then(({ data }) => {
          this.context.commit(Mutations.SET_SCHEDULE_ITEM.LIST, data.data);
          return Promise.resolve();
        })
        .catch(({ response }) => {
          let message = "Could not get schedule items.";
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
  [Actions.SCHEDULE_ITEM.CREATE](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("schedule-items", payload)
        .then(({ data }) => {
          return Promise.resolve();
        })
        .catch(({ response }) => {
          let message = "Could not create schedule item.";
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
  [Actions.SCHEDULE_ITEM.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.update("schedule-items", item.id, item)
        .then(({ data }) => {
          return Promise.resolve();
        })
        .catch(({ response }) => {
          let message = "Could not update schedule item.";
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
  [Actions.SCHEDULE_ITEM.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("schedule-items/" + id)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          let message = "Could not delete schedule item.";
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
