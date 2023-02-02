import BillingApiService from "@/core/services/BillingApiService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface MbsItemResponse {
  success: boolean;
  data: MbsItemDataResponse;
}

export interface MbsItemDataResponse {
  total: number;
  items: Array<MbsItem>;
}

export interface MbsItem {
  id: number;
  name: string;
  description: string;
  mbs_item_number: number;
  mbs_sub_item_number: number | null;
  mbs_code_group_id: number;
  item_type: string | null;
  fee_type: string | null;
  schedule_fee: number;
  provider_type: string | null;
  emsn_cap: number | null;
  emsn_fixed_cap_amount: number | null;
  emsn_maximum_cap: number | null;
  emsn_percentage_cap: number | null;
  emsn_description: string | null;
  derived_fee_description: string | null;
  benefit_type: BenefitType | null;
  benefit_100: number | null;
  benefit_85: number | null;
  benefit_75: number | null;
  basic_units: number | null;
  emsn_starts_at: string | null;
  emsn_ends_at: string | null;
  derived_fee_starts_at: string | null;
  benefit_starts_at: string | null;
  fee_starts_at: string | null;
  starts_at: number;
  ends_at: string | null;
  mbs_code_group: MbsCodeGroup;
  label_name: string;
}

export interface BenefitType {
  id: number;
  benefit_code: string;
  benefit_description: string;
}

export interface MbsCodeGroup {
  id: number;
  mbs_category_code: number;
  mbs_category_name: string;
  mbs_item_group: string;
  mbs_item_group_name: string;
  mbs_item_subgroup: string | null;
  mbs_item_subgroup_name: string | null;
  mbs_item_subheading: number | null;
  mbs_item_subheading_name: string | null;
  broad_type_of_service: number | null;
  broad_type_of_service_description: string | null;
}

export interface MbsItemInfo {
  errors: unknown;
  mbsItemResponse: MbsItemResponse;
}

@Module
export default class MbsModule extends VuexModule implements MbsItemInfo {
  errors = {};
  mbsItemResponse = {} as MbsItemResponse;

  /**
   * Get MBS item response
   * @returns MbsItemResponse
   */
  get mbsItems(): MbsItemDataResponse {
    return this.mbsItemResponse.data;
  }

  @Mutation
  [Mutations.SET_MBS.LIST](data) {
    this.mbsItemResponse = data;
  }

  @Mutation
  [Mutations.SET_ERROR](error) {
    this.errors = { ...error, status: "failed" };
  }

  @Action
  async [Actions.MBS.LIST](search) {
    if (await BillingApiService.setHeader()) {
      return BillingApiService.get("api/mbs/items", "", search)
        .then(({ data }) => {
          if (data.success) {
            this.context.commit(Mutations.SET_MBS.LIST, data);
          } else {
            throw data;
          }
        })
        .catch(({ response }) => {
          this.context.commit(Mutations.SET_ERROR, response.data.data);
          return Promise.reject();
        });
    }
  }
}
