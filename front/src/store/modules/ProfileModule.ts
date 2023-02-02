import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";
import IUserProfile from "../interfaces/IUserProfile";

export interface ProfileInfo {
  profile: IUserProfile;
}

@Module
export default class ProfileModule extends VuexModule implements ProfileInfo {
  profile = {} as IUserProfile;

  /**
   * Get current user object
   * @returns profileSelectedData
   */
  get userProfile(): IUserProfile {
    return this.profile;
  }

  @Mutation
  [Mutations.SET_PROFILE](data) {
    this.profile = data;
  }

  @Action
  [Actions.PROFILE.VIEW]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("profile")
        .then(({ data }) => {
          this.context.commit(Mutations.SET_PROFILE, data.data);
        })
        .catch(({ response }) => {
          return displayServerError(response, "viewing user profile");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.PROFILE.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("update-profile", item)
        .then(() => {
          return displaySuccessToast("Profile Updated");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating user profile");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.PROFILE.UPDATE_PASSWORD](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("users/change-password", item)
        .then(() => {
          return displaySuccessToast("Password Updated");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating user password");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.PROFILE.UPDATE_SIGNATURE](data) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("profile/signature", data)
        .then(() => {
          return displaySuccessToast("Signature Updated");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating user signature");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
