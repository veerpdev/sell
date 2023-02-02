import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import {
  AppointmentActions,
  AppointmentMutations,
} from "../enums/StoreAppointmentEnums";
import IAppointmentType from "../interfaces/IAppointmentType";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";
export interface AptTypesInfo {
  aptTypesData: Array<IAppointmentType>;
  aptTypesSelectData: IAppointmentType;
}

@Module
export default class AptTypesModule extends VuexModule implements AptTypesInfo {
  aptTypesData = [] as Array<IAppointmentType>;
  aptTypesSelectData = {} as IAppointmentType;

  /**
   * Get current user object
   * @returns AdminList
   */
  get getAptTypesList(): Array<IAppointmentType> {
    return this.aptTypesData;
  }

  /**
   * Get current user object
   * @returns SelectedaptTypesData
   */
  get getAptTypesSelected(): IAppointmentType {
    return this.aptTypesSelectData;
  }

  @Mutation
  [AppointmentMutations.SET_APT.TYPES.LIST](aptTypesData) {
    this.aptTypesData = aptTypesData;
  }

  @Mutation
  [AppointmentMutations.SET_APT.TYPES.SELECT](data) {
    this.aptTypesSelectData = data;
  }

  @Action
  [AppointmentActions.APPOINTMENT_TYPES.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("appointment-types")
        .then(({ data }) => {
          this.context.commit(
            AppointmentMutations.SET_APT.TYPES.LIST,
            data.data
          );
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing appointment types");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [AppointmentActions.APPOINTMENT_TYPES.CREATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("appointment-types", item)
        .then(({ data }) => {
          return displaySuccessModal("New Appointment Type Created");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Creating an appointment type");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [AppointmentActions.APPOINTMENT_TYPES.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.update("appointment-types", item.id, item)
        .then(({ data }) => {
          return displaySuccessModal("Appointment Type Updated");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating an appointment type");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [AppointmentActions.APPOINTMENT_TYPES.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.delete("appointment-types/" + id)
        .then(({ data }) => {
          return displaySuccessToast("Appointment Type Deleted");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Deleting an appointment type");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
