import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";
export interface IAptTimeRequirement {
  id: number;
  title: string;
  type: string;
  time: string;
}

export interface AptTimeRequireInfo {
  aptTimeRequireData: Array<IAptTimeRequirement>;
  aptTimeRequireSelectData: IAptTimeRequirement;
}

@Module
export default class AptTimeRequireModule
  extends VuexModule
  implements AptTimeRequireInfo
{
  aptTimeRequireData = [] as Array<IAptTimeRequirement>;
  aptTimeRequireSelectData = {} as IAptTimeRequirement;

  /**
   * Get current user object
   * @returns AdminList
   */
  get getAptTimeRequireList(): Array<IAptTimeRequirement> {
    return this.aptTimeRequireData;
  }

  /**
   * Get current user object
   * @returns SelectedaptTimeRequireData
   */
  get getAptTimeRequireSelected(): IAptTimeRequirement {
    return this.aptTimeRequireSelectData;
  }

  @Mutation
  [Mutations.SET_APT_TIME_REQUIREMENT.LIST](aptTimeRequireData) {
    this.aptTimeRequireData = aptTimeRequireData;
  }

  @Mutation
  [Mutations.SET_APT_TIME_REQUIREMENT.SELECT](data) {
    this.aptTimeRequireSelectData = data;
  }

  @Action
  [Actions.APT_TIME_REQUIREMENT.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("appointment-time-requirements")
        .then(({ data }) => {
          this.context.commit(
            Mutations.SET_APT_TIME_REQUIREMENT.LIST,
            data.data
          );
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Listing appointment time requirements"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.APT_TIME_REQUIREMENT.CREATE](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("appointment-time-requirements", payload)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Creating an appointment time requirement"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.APT_TIME_REQUIREMENT.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("appointment-time-requirements", item.id, item)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Updating an appointment time requirement"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.APT_TIME_REQUIREMENT.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("appointment-time-requirements/" + id)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Deleting an appointment time requirement"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
