<template>
  <SettingsButton />
  <div class="card w-full w-xl-75 mx-auto">
    <div class="card-header border-0 pt-6">
      <div class="card-title">
        <div class="d-flex align-items-center position-relative my-1">
          <span class="svg-icon svg-icon-1 position-absolute ms-6">
            <InlineSVG icon="search" />
          </span>
          <input
            type="text"
            data-kt-subscription-table-filter="search"
            class="form-control form-control-solid w-350px ps-14"
            placeholder="Search Doctor Address Books"
            v-model="filterAndSort.searchText"
          />
        </div>
      </div>
      <!--begin::Add button-->
      <div class="card-toolbar">
        <router-link
          to="/settings/doctor-address-books/create"
          type="button"
          class="text-nowrap btn btn-light-primary ms-auto"
        >
          <span class="svg-icon svg-icon-2">
            <InlineSVG icon="plus" />
          </span>
          Add
        </router-link>
      </div>
      <!--end::Add button-->
    </div>
    <div class="card-body pt-0">
      <Datatable
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="10"
        :loading="loading"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-provider_no="{ row: item }">
          <span class="text-uppercase">{{ item.provider_no }}</span>
        </template>
        <template v-slot:cell-full_name="{ row: item }">
          {{ item.full_name }}
        </template>
        <template v-slot:cell-details="{ row: item }">
          {{ item.practice_name }}<br />
          {{ item.practice_number }}<br v-if="item.practice_number" />
          {{ item.practice_email }}<br v-if="item.practice_email" />
          {{ item.practice_address }}
        </template>
        <template v-slot:cell-preferred_contact_method="{ row: item }">
          {{ item.preferred_communication_method }}
        </template>
        <template v-slot:cell-action="{ row: item }">
          <button
            @click="handleEdit(item)"
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
          >
            <span class="svg-icon svg-icon-3">
              <InlineSVG icon="pencil" />
            </span>
          </button>

          <button
            @click="handleDelete(item.id)"
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-5"
          >
            <span class="svg-icon svg-icon-3">
              <InlineSVG icon="bin" />
            </span>
          </button>
        </template>
      </Datatable>
    </div>
  </div>
</template>

<script>
import {
  defineComponent,
  onMounted,
  ref,
  computed,
  watchEffect,
  reactive,
  watch,
} from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import { Actions } from "@/store/enums/StoreEnums";
import SettingsButton from "@/components/SettingsButton.vue";
export default defineComponent({
  name: "doctor-address-books",

  components: {
    Datatable,
    SettingsButton,
  },

  setup() {
    const store = useStore();
    const router = useRouter();
    const filterAndSort = reactive({
      searchText: "",
    });
    const tableHeader = ref([
      {
        name: "Provider Number",
        key: "provider_no",
        sortable: true,
      },
      {
        name: "Full Name",
        key: "full_name",
        sortable: true,
      },
      {
        name: "Practice Details",
        key: "details",
      },
      {
        name: "Preferred Communication",
        key: "preferred_contact_method",
      },
      {
        name: "Action",
        key: "action",
      },
    ]);
    const tableData = ref([]);
    const filteredData = ref([]);
    const loading = ref(true);
    const doctorAddressBooks = computed(
      () => store.getters.getDoctorAddressBookList
    );

    const handleEdit = (item) => {
      router.push({ name: "editRefDoctors", params: { id: item.id } });
    };

    const handleDelete = (id) => {
      store.dispatch(Actions.DOCTOR_ADDRESS_BOOK.DELETE, id).then(() => {
        store.dispatch(Actions.DOCTOR_ADDRESS_BOOK.LIST);
      });
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Doctor Address Books", ["Settings"]);
    });

    watchEffect(() => {
      tableData.value = doctorAddressBooks;
      loading.value = false;
    });

    const applyFilterAndSort = () => {
      if (filterAndSort.searchText != "") {
        filteredData.value = doctorAddressBooks.value.filter((org) => {
          if (
            org.provider_no
              .toLowerCase()
              .search(filterAndSort.searchText.toLowerCase()) >= 0
          ) {
            return true;
          }

          if (
            org.full_name
              .toLowerCase()
              .search(filterAndSort.searchText.toLowerCase()) >= 0
          ) {
            return true;
          }

          return false;
        });
      } else {
        filteredData.value = doctorAddressBooks.value;
      }
      tableData.value = filteredData;
    };

    watchEffect(() => {
      applyFilterAndSort();
    });

    watch(filterAndSort, () => {
      applyFilterAndSort();
    });

    return { tableHeader, tableData, handleEdit, handleDelete, filterAndSort };
  },
});
</script>
