import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { CodingActions, CodingMutations } from "@/store/enums/StoreCodingEnums";
import { Mutations } from "@/store/enums/StoreEnums";
import axios from "axios";
import { Module, Action, VuexModule, Mutation } from "vuex-module-decorators";
import { displayServerError } from "@/helpers/helpers";

interface IApt {
  id: number;
}

interface AptInfo {
  aptData: Array<IApt>;
  aptSelectData: IApt;
}

@Module
export default class CodingModule extends VuexModule implements AptInfo {
  aptData = [] as Array<IApt>;
  aptSelectData = {} as IApt;

  /**
   * Get list of appointment to be coded
   * @returns AdminList
   */
  get getCodingAptList(): Array<IApt> {
    return this.aptData;
  }

  /**
   * Get selected appointment to be coded
   * @returns AdminData
   */
  get getCodingAptSelect(): IApt {
    return this.aptSelectData;
  }

  @Mutation
  [CodingMutations.SET_LIST](aptData) {
    this.aptData = aptData;
  }

  @Mutation
  [CodingMutations.SET_SELECT](aptData) {
    this.aptSelectData = aptData;
  }

  @Action
  [CodingActions.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("coding")
        .then(({ data }) => {
          this.context.commit(CodingMutations.SET_LIST, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing all codings");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [CodingActions.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.put("coding/" + item.id, item)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Storing a coading update form");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [CodingActions.SEARCH_DIAGNOSES](searchParam) {
    console.log("SEARCH:" + searchParam);

    try {
      const data = axios.get(
        "https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search?sf=code,name&terms=" +
          searchParam
      );
      return data;
    } catch (error) {
      return displayServerError(error, "Storing a search diagnoses form");
    }
  }

  @Action
  [CodingActions.CHECK_APPOINTMENTS_COMPLETE](data) {
    return ApiService.post("coding/check-appointments-complete", data)
      .then(({ data }) => {
        return data.data;
      })
      .catch(({ response }) => {
        return displayServerError(response, "Storing a pre admissions form");
      });
  }

  @Action
  [CodingActions.GENERATE_CODING_REPORT](payload) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("generate-coding-report", payload, {
        responseType: "blob",
      })
        .then(({ data }) => {
          return data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Generating a coding report");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
