import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Mutations } from "@/store/enums/StoreEnums";
import {
  AppointmentActions,
  AppointmentMutations,
} from "@/store/enums/StoreAppointmentEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import IClinic from "@/store/interfaces/IClinic";

export interface IWorkhour {
  available: boolean;
  locations: Array<number>;
  time_slot: Array<string>;
}

export interface ITimeSchedule {
  id: number;
  available: boolean;
  organization_id: number;
  clinic_id: number;
  user_id: number;
  specialist_name: string;
  anesthetist_id: number;
  anesthetist_name: string;
  room_id: number;
  is_wait_listed: boolean;
  restriction: string;
  category: string;
  week_day: string;
  date: string;
  start_time: string;
  end_time: string;
  clinic_name: string;
  clinic: IClinic;
  anesthetist: ISpecialist;
}

export interface ISpecialist {
  id: string;
  name: string;
  full_name: string;
  work_hours: Array<IWorkhour>;
  hrm_work_schedule: Array<ITimeSchedule>;
}

export interface IBookingData {
  time_slots: Array<string>;
  ava_specialist: Array<ISpecialist>;
  selected_specialist: ISpecialist;
  overlapping_cnt: number;
}

export interface ISearchVariable {
  date: string;
  specialists: Array<string>;
}

export interface BookingInfo {
  bookingData: IBookingData;
  filteredData: IBookingData;
  availableAppointmentList: IBookingData;
  filteredSpecialists: IBookingData;
  searchVal: ISearchVariable;
}

@Module
export default class BooingModule extends VuexModule implements BookingInfo {
  bookingData = {} as IBookingData;
  filteredData = {} as IBookingData;
  monthAvailabilities;
  availableAppointmentList = {} as IBookingData;
  filteredSpecialists = {} as IBookingData;
  searchVal = {} as ISearchVariable;

  /**
   * Get current user object
   * @returns SelectedclinicsData
   */
  get getMonthAvailabilities() {
    return this.monthAvailabilities;
  }

  /**
   * Get current user object
   * @returns SelectedclinicsData
   */
  get bookingDatas(): IBookingData {
    return this.bookingData;
  }

  /**
   * Get current user object
   * @returns SelectedclinicsData
   */
  get getFilteredData(): IBookingData {
    return this.filteredData;
  }

  /**
   * Get current user object
   * @returns availableAppointmentList
   */
  get getAvailableAppointmentList(): IBookingData {
    return this.availableAppointmentList;
  }

  /**
   * Get current user object
   * @returns SelectedclinicsData
   */
  get getAvailableSPTData(): IBookingData {
    return this.filteredSpecialists;
  }

  /**
   * Get current user object
   * @returns SelectedclinicsData
   */
  get getSearchVariable(): ISearchVariable {
    return this.searchVal;
  }

  @Mutation
  [AppointmentMutations.SET_BOOKING.SELECT](data: IBookingData) {
    this.bookingData = data;
  }

  @Mutation
  [AppointmentMutations.SET_BOOKING.SEARCH.SPECIALISTS](data: IBookingData) {
    this.filteredData = data;
  }

  @Mutation
  [AppointmentMutations.SET_BOOKING.SEARCH.NEXT_APTS](data: IBookingData) {
    this.availableAppointmentList = data;
  }

  @Mutation
  [AppointmentMutations.SET_BOOKING.SEARCH.VARIABLE](data: ISearchVariable) {
    this.searchVal = data;
  }

  @Mutation
  [AppointmentMutations.SET_BOOKING.SEARCH.FILLTEREDSPECIALISTS](
    data: IBookingData
  ) {
    this.filteredSpecialists = data;
  }

  @Mutation
  [AppointmentMutations.SET_MONTH_AVAILABILITIES](data: IBookingData) {
    this.monthAvailabilities = data;
  }

  @Action
  [AppointmentActions.BOOKING.SEARCH.NEXT_APT](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.query("available-timeslots", { params: payload })
        .then(({ data }) => {
          this.context.commit(
            AppointmentMutations.SET_BOOKING.SEARCH.NEXT_APTS,
            data.data
          );
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [AppointmentActions.MONTH_AVAILABILITIES](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.query("appointment-month-availabilities", { params: payload })
        .then(({ data }) => {
          this.context.commit(
            AppointmentMutations.SET_MONTH_AVAILABILITIES,
            data.data
          );
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [AppointmentActions.BOOKING.SEARCH.SPECIALISTS](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.query("appointments/specialists", { params: payload })
        .then(({ data }) => {
          this.context.commit(
            AppointmentMutations.SET_BOOKING.SEARCH.SPECIALISTS,
            data.data
          );
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
