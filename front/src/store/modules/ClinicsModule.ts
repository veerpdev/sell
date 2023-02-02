import ApiService from "@/core/services/ApiService";
import BillingApiService from "@/core/services/BillingApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";
import IClinic from "../interfaces/IClinic";

export interface IRooms {
  id: number;
}

export interface ClinicsInfo {
  clinicsData: Array<IClinic>;
  clinicsSelectData: IClinic;
  roomsData: Array<IRooms>;
  roomsSelectData: IRooms;
}

@Module
export default class ClinicsModule extends VuexModule implements ClinicsInfo {
  clinicsData = [] as Array<IClinic>;
  clinicsSelectData = {} as IClinic;
  roomsData = [] as Array<IRooms>;
  roomsSelectData = {} as IRooms;

  /**
   * Get current user object
   * @returns AdminList
   */
  get clinicsList(): Array<IClinic> {
    return this.clinicsData;
  }

  /**
   * Get current user object
   * @returns SelectedclinicsData
   */
  get clinicsSelected(): IClinic {
    return this.clinicsSelectData;
  }

  /**
   * Get current rooms object
   * @returns RoomsList
   */
  get roomsList(): Array<IRooms> {
    return this.roomsData;
  }

  /**
   * Get current user object
   * @returns SelectedclinicsData
   */
  get roomsSelected(): IRooms {
    return this.roomsSelectData;
  }

  @Mutation
  [Mutations.SET_CLINICS.LIST](clinicsData) {
    this.clinicsData = clinicsData;
  }

  @Mutation
  [Mutations.SET_CLINICS.SELECT](data) {
    this.clinicsSelectData = data;
  }

  @Mutation
  [Mutations.SET_CLINICS.LISTROOMS](roomsData) {
    this.roomsData = roomsData;
  }

  @Mutation
  [Mutations.SET_CLINICS.SELECTROOMS](data) {
    this.roomsSelectData = data;
  }

  @Action
  [Actions.CLINICS.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("clinics")
        .then(({ data }) => {
          this.context.commit(Mutations.SET_CLINICS.LIST, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing all clinics");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.CLINICS.CREATE](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("clinics", payload)
        .then(({ data }) => {
          if (data.data) {
            displaySuccessToast("Clinic created");
          } else {
            displayServerError(data.message, "Creating a clinic");
          }
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Creating a clinic");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.CLINICS.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.update("clinics", item.id, item)
        .then(() => {
          return displaySuccessToast("Clinic Updated");
        })
        .catch(({ response }) => {
          displayServerError(response, "Updating a clinic");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.CLINICS.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("clinics/" + id)
        .then(() => {
          return displaySuccessToast("Clinic deleted");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Deleting a clinic");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.CLINICS.ROOMS.LIST](clinic_id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("clinics/" + clinic_id + "/rooms", "", { id: clinic_id })
        .then(({ data }) => {
          this.context.commit(Mutations.SET_CLINICS.LISTROOMS, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing clinic rooms");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.CLINICS.ROOMS.CREATE](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("clinics/" + payload.clinic_id + "/rooms", payload)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Creating a clinic room");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.CLINICS.ROOMS.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("clinics/" + item.clinic_id + "/rooms", item.id, item)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating a clinic room");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.CLINICS.ROOMS.DELETE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("clinics/" + item.clinic_id + "/rooms/" + item.id)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Deleting a clinic room");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  async [Actions.CLINICS.MINOR_ID](clinic) {
    if (await BillingApiService.setHeader()) {
      return BillingApiService.post(
        `api/locations/${clinic.organization_id}/create`,
        clinic
      )
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          this.context.commit(Mutations.SET_ERROR, response.data.data);
          return Promise.reject();
        });
    }
  }
}
