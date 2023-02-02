import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";

export interface IOutgoing {
  id: string;
}

export interface IOutgoingInfo {
  outgoingData: Array<IOutgoing>;
  outgoingSelectData: IOutgoing;
}

@Module
export default class OutgoingModule
  extends VuexModule
  implements IOutgoingInfo
{
  outgoingData = [] as Array<IOutgoing>;
  outgoingSelectData = {} as IOutgoing;

  /**
   * Get current outgoing log List
   * @returns Outgoing
   */
  get getOutgoingList(): Array<IOutgoing> {
    return this.outgoingData;
  }

  /**
   * Get current outgoing log List
   * @returns Outgoing
   */
  get getOutgoingSelectedList(): IOutgoing {
    return this.outgoingSelectData;
  }

  @Mutation
  [Mutations.SET_OUTGOING.LIST](outgoingData) {
    this.outgoingData = outgoingData;
  }

  @Mutation
  [Mutations.SET_OUTGOING.SELECT](outgoingData) {
    this.outgoingSelectData = outgoingData;
  }

  @Action
  [Actions.OUTGOING.LIST](params) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.get("communication/outgoing-log", "", params)
        .then(({ data }) => {
          this.context.commit(Mutations.SET_OUTGOING.LIST, data.data);
          return data.data;
        })
        .catch(({ response }) => {
          console.error(["error", response]);
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
