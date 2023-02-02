import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Mutations } from "@/store/enums/StoreEnums";
import { HRMActions, HRMMutations } from "@/store/enums/StoreHRMEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import { IUser } from "@/store/modules/UserModule";

export interface IHRMWeeklyScheduleTimeslot {
  id: number;
}

export interface IHRMWeeklyScheduleTemplate {
  id: number;
}

export interface IHRMWeeklyScheduleTemplates {
  hrmScheduleSelectData: IHRMWeeklyScheduleTemplate;
  hrmAnesthetists: Array<IHRMWeeklyScheduleTemplate>;
  hrmScheduleListData: Array<IUser>;
  hrmSelectedScheduleListData: Array<IHRMWeeklyScheduleTemplate>;
  hrmData: Array<IHRMWeeklyScheduleTemplate>;
  hrmSelectedData: IHRMWeeklyScheduleTemplate;
}

@Module
export default class HRMModule
  extends VuexModule
  implements IHRMWeeklyScheduleTemplates
{
  hrmScheduleSelectData = {} as IHRMWeeklyScheduleTemplate;
  hrmAnesthetists = [] as Array<IHRMWeeklyScheduleTemplate>;
  hrmData = [] as Array<IHRMWeeklyScheduleTemplate>;
  hrmSelectedData = {} as IHRMWeeklyScheduleTemplate;
  hrmScheduleListData = [] as Array<IUser>;
  hrmSelectedScheduleListData = [] as Array<IHRMWeeklyScheduleTemplate>;

  get hrmScheduleSelected(): IHRMWeeklyScheduleTemplate {
    return this.hrmScheduleSelectData;
  }

  get hrmScheduleList(): Array<IUser> {
    return this.hrmScheduleListData;
  }

  get hrmSelectedScheduleList(): Array<IHRMWeeklyScheduleTemplate> {
    return this.hrmSelectedScheduleListData;
  }

  get hrmAnesthetist(): Array<IHRMWeeklyScheduleTimeslot> {
    return this.hrmAnesthetists;
  }

  get hrmDataList(): Array<IHRMWeeklyScheduleTemplate> {
    return this.hrmData;
  }

  get hrmDataSelected(): IHRMWeeklyScheduleTemplate {
    return this.hrmSelectedData;
  }

  @Mutation
  [HRMMutations.SCHEDULE.SET_SELECT](data) {
    this.hrmScheduleSelectData = data;
  }

  @Mutation
  [HRMMutations.SCHEDULE.SET_TIMESLOT](data) {
    this.hrmSelectedScheduleListData = data;
  }

  @Mutation
  [HRMMutations.ANESTHETIST.SET_LIST](data) {
    this.hrmAnesthetists = data;
  }

  @Mutation
  [HRMMutations.SCHEDULE.SET_LIST](data) {
    this.hrmScheduleListData = data;
  }

  @Mutation
  [HRMMutations.DATA.SET_SELECT](data) {
    this.hrmSelectedData = data;
  }

  @Mutation
  [HRMMutations.DATA.SET_LIST](data) {
    this.hrmData = data;
  }

  @Action
  [HRMActions.SCHEDULE_TEMPLATE.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("hrm/schedule-template")
        .then(({ data }) => {
          this.context.commit(HRMMutations.SCHEDULE.SET_LIST, data.data);
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
  [HRMActions.SCHEDULE_TEMPLATE.CREATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("hrm/schedule-template", item)
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
  [HRMActions.SCHEDULE_TEMPLATE.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("hrm/schedule-template", item.id, item)
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
  [HRMActions.ANESTHETIST.LIST](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.query("hrm/anesthetists", { params: payload })
        .then(({ data }) => {
          this.context.commit(HRMMutations.ANESTHETIST.SET_LIST, data.data);
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [HRMActions.WEEKLY_SCHEDULE.LIST](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.query("hrm/weekly-schedule", {
        params: {
          date: data.date,
        },
      })
        .then(({ data }) => {
          this.context.commit(HRMMutations.SCHEDULE.SET_LIST, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      // this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [HRMActions.WEEKLY_SCHEDULE.CREATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("hrm/weekly-schedule", item)
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
  [HRMActions.WEEKLY_SCHEDULE.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("hrm/weekly-schedule", item.id, item)
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
  [HRMActions.EMPLOYEE_LEAVE.LIST](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.query("hrm/employee-leaves", { params: payload })
        .then(({ data }) => {
          this.context.commit(HRMMutations.DATA.SET_LIST, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      // this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [HRMActions.EMPLOYEE_LEAVE.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("hrm/employee-leaves", item.id, item)
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
  [HRMActions.EMPLOYEE_LEAVE.CREATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("hrm/employee-leaves", item)
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
  [HRMActions.EMPLOYEE_LEAVE.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("hrm/employee-leaves/" + id)
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
  [HRMActions.DEALLOCATE_APPOINTMENTS.LIST](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.query("hrm/deallocate-appointments", { params: payload })
        .then(({ data }) => {
          this.context.commit(HRMMutations.DATA.SET_LIST, {
            specialistAppointments: data.specialistAppointments,
            anesthetistAppointments: data.anesthetistAppointments,
          });
          return data.data;
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      // this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
