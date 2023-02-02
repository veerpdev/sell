import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import { ITimeSchedule } from "@/store/modules/BookingModule";

export interface ISpecialist {
  id: number;
  name: string;
  full_name: string;
  updated_at: string;
  created_at: string;
  schedule_timeslots: Array<ITimeSchedule>;
  hrm_work_schedule: Array<ITimeSchedule>;
}

export interface SpecalistInfo {
  specialistsData: Array<ISpecialist>;
  searchSpecialistsData: Array<ISpecialist>;
  specialistsSelectData: ISpecialist;
}

@Module
export default class SpecialistModule
  extends VuexModule
  implements SpecalistInfo
{
  specialistsData = [] as Array<ISpecialist>;
  searchSpecialistsData = [] as Array<ISpecialist>;
  specialistsSelectData = {} as ISpecialist;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  private specialistDate: any;

  /**
   * Get current user object
   * @returns SpecalistList
   */
  get getSpecialistList(): Array<ISpecialist> {
    return this.specialistsData;
  }

  /**
   * Get current user object
   * @returns SpecalistList
   */
  get getSpecialistSelected(): ISpecialist {
    return this.specialistsSelectData;
  }

  /**
   * Get current searched specialist list object
   * @returns SpecalistList
   */
  get getSearchSpecialistList(): Array<ISpecialist> {
    return this.searchSpecialistsData;
  }

  @Mutation
  [Mutations.SET_SPECIALIST.LIST](specialistsData) {
    this.specialistsData = specialistsData;
  }

  @Mutation
  [Mutations.SET_SPECIALIST.SELECT](data) {
    this.specialistsSelectData = data;
  }

  @Mutation
  [Mutations.SET_SPECIALIST.SEARCH.SEARCH_LIST](specialistsData) {
    this.searchSpecialistsData = specialistsData;
  }

  @Action
  [Actions.SPECIALIST.LIST](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.query("users", {
        params: { role_id: 5 },
      })
        .then(({ data }) => {
          this.context.commit(Mutations.SET_SPECIALIST.LIST, data.data);
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
  [Actions.SPECIALIST.CREATE](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("specialists", payload)
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
  [Actions.SPECIALIST.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("specialists", item.id, item)
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
  [Actions.SPECIALIST.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("specialists/" + id)
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
  [Actions.SPECIALIST.SEARCH.LIST](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("users", "", data)
        .then(({ data }) => {
          this.context.commit(
            Mutations.SET_SPECIALIST.SEARCH.SEARCH_LIST,
            data.data
          );
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
}
