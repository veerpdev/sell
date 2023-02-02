import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Module, Action, Mutation, VuexModule } from "vuex-module-decorators";
import {
  displayServerError,
  displaySuccessModal,
  displaySuccessToast,
} from "@/helpers/helpers";
import IDoctorAddressBook from "../interfaces/IDoctorAddressBook";

export interface DoctorAddressBookInfo {
  doctorAddressBookData: Array<IDoctorAddressBook>;
}

@Module
export default class DoctorAddressBookModule
  extends VuexModule
  implements DoctorAddressBookInfo
{
  doctorAddressBookData = [] as Array<IDoctorAddressBook>;

  /**
   * Get current user object
   * @returns AdminList
   */
  get getDoctorAddressBookList(): Array<IDoctorAddressBook> {
    return this.doctorAddressBookData;
  }

  @Mutation
  [Mutations.SET_DOCTOR_ADDRESS_BOOK.LIST](doctorAddressBookData) {
    this.doctorAddressBookData = doctorAddressBookData;
  }

  @Action
  [Actions.DOCTOR_ADDRESS_BOOK.LIST]() {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.get("doctor-address-book")
        .then(({ data }) => {
          this.context.commit(
            Mutations.SET_DOCTOR_ADDRESS_BOOK.LIST,
            data.data
          );
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Listing doctor address book");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.DOCTOR_ADDRESS_BOOK.FIND_BY_PROVIDER_NO](providerNo) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      return ApiService.post("doctor-address-book/find-by-provider-no", {
        provider_no: providerNo,
      })
        .then(({ data }) => {
          return data.data[0];
        })
        .catch(({ response }) => {
          return displayServerError(
            response,
            "Finding doctor address book by provider number"
          );
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.DOCTOR_ADDRESS_BOOK.CREATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.post("doctor-address-book", item)
        .then(({ data }) => {
          return data.data;
        })
        .catch(({ response }) => {
          return displayServerError(response, "Creating a doctor address");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.DOCTOR_ADDRESS_BOOK.UPDATE](item) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.update("doctor-address-book", item.id, item)
        .then(() => {
          return displaySuccessToast("Doctor address updated");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Updating a doctor address");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }

  @Action
  [Actions.DOCTOR_ADDRESS_BOOK.DELETE](id) {
    if (JwtService.getToken()) {
      ApiService.setHeader();
      ApiService.delete("doctor-address-book/" + id)
        .then(() => {
          return displaySuccessToast("Doctor address deleted");
        })
        .catch(({ response }) => {
          return displayServerError(response, "Deleting a doctor address");
        });
    } else {
      this.context.commit(Mutations.PURGE_AUTH);
    }
  }
}
