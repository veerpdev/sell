<template>
  <div class="card w-75 mx-auto">
    <div class="card-header border-0 pt-6">
      <!--begin::Card title-->
      <div class="card-title">
        <!--begin::Search-->
        <div class="d-flex align-items-center position-relative my-1">
          <span class="svg-icon svg-icon-1 position-absolute ms-6">
            <InlineSVG icon="search" />
          </span>
          <input
            type="text"
            data-kt-subscription-table-filter="search"
            class="form-control form-control-solid w-250px ps-14"
            placeholder="Search Organisations"
            v-model="filterAndSort.searchText"
          />
        </div>
        <!--end::Search-->
      </div>
      <!--begin::Card title-->

      <!--begin::Card toolbar-->
      <div class="card-toolbar">
        <!--begin::Toolbar-->
        <div
          class="d-flex justify-content-end"
          data-kt-subscription-table-toolbar="base"
        >
          <!--begin::Add subscription-->
          <router-link to="/organisations/create" class="btn btn-primary">
            <span class="svg-icon svg-icon-2">
              <InlineSVG icon="plus" />
            </span>
            Add New Organisation
          </router-link>
          <!--end::Add subscription-->
        </div>
        <!--end::Toolbar-->
      </div>
      <!--end::Card toolbar-->
    </div>
    <div class="card-body pt-0">
      <Datatable
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="5"
        :loading="loading"
        :enable-items-per-page-dropdown="false"
      >
        <template v-slot:cell-name="{ row: organization }">
          <div class="d-flex align-items-center">
            <div class="symbol symbol-50px me-2">
              <img
                alt="Org Logo"
                v-if="organization.logo"
                :src="organization.logo"
                class="h-50 align-self-center"
              />
            </div>
            <span class="text-dark fw-bolder text-hover-primary m-2 fs-3">
              {{ organization.name }}
            </span>
          </div>
        </template>
        <template v-slot:cell-contact="{ row: organization }">
          <span class="text-dark fw-bolder text-hover-primary mb-1 fs-6">{{
            organization.username
          }}</span>
          <span class="text-muted fw-bold d-block">{{
            organization.mobile_number
          }}</span>
          <span class="text-muted fw-bold d-block">{{
            organization.email
          }}</span>
        </template>
        <template v-slot:cell-stats="{ row: organization }">
          <div class="d-flex flex-column">
            <div class="mb-2">
              <em class="bi bi-building fs-1x"></em>
              <span class="">
                {{ organization.clinic_count }} /
                {{ organization.max_clinics }}</span
              >
            </div>
            <div>
              <em class="bi bi-person-fill fs-1x"></em>
              <span class="">
                {{ organization.user_count }} /
                {{ organization.max_employees }}</span
              >
            </div>
          </div>
        </template>

        <template v-slot:cell-action="{ row: organization }">
          <div class="d-flex flex-row gap-1">
            <IconButton
              @click="handleEdit(organization)"
              :iconSRC="icons.pencil"
              tooltip="Edit organisation"
            />

            <IconButton
              @click="handleDelete(organization)"
              :iconSRC="icons.bin"
              tooltip="Delete organisation"
            />
          </div>
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
  watch,
  reactive,
} from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions } from "@/store/enums/StoreEnums";
import icons from "@/core/data/icons";
export default defineComponent({
  name: "org-main",

  components: {
    Datatable,
  },

  setup() {
    const store = useStore();
    const router = useRouter();
    const tableHeader = ref([
      {
        name: "Organisation Name",
        key: "name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Contact",
        key: "contact",
        sortable: true,
        searchable: true,
      },
      {
        name: "Stats",
        key: "stats",
        sortable: false,
        searchable: false,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);

    const filterAndSort = reactive({
      searchText: "",
    });

    const tableData = ref([]);
    const filteredData = ref([]);
    const orgList = computed(() => store.getters.orgList);
    const currentUser = computed(() => store.getters.currentUser);
    const loading = ref(true);

    const handleEdit = (item) => {
      router.push({ name: "editOrganisation", params: { id: item.id } });
    };

    const deleteAfterConfirmation = (item) => {
      const html =
        '<p class="fs-2">Please type <b>' +
        item.name +
        "</b> to confirm</p><br/>";

      Swal.fire({
        input: "text",
        inputAttributes: {
          autocapitalize: "off",
          placeholder: "Organization Name",
        },
        html: html,
        icon: "info",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Delete Organisation",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light-primary",
        },
        preConfirm: async (data) => {
          if (data.toLowerCase() == item.name.toLowerCase()) {
            return "success";
          }

          return false;
        },
      }).then((result) => {
        if (result.value == "success") {
          store.dispatch(Actions.ORG.DELETE, item.id).then(() => {
            store.dispatch(Actions.ORG.LIST).then(() => {
              loading.value = false;
            });
          });
        }
      });
    };

    const handleDelete = (item) => {
      loading.value = true;

      const html = "<h3>To confirm it's you</h3><br/>";

      Swal.fire({
        input: "password",
        inputAttributes: {
          autocapitalize: "off",
          placeholder: "Password",
        },
        html: html,
        icon: "info",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Confirm",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light-primary",
        },
        preConfirm: async (data) => {
          await store.dispatch(Actions.LOGIN, {
            username: currentUser.value.username,
            password: data,
          });

          const error = store.getters.getErrors["status"];

          if (error != "failed") {
            return "success";
          } else {
            Swal.fire({
              text: "Incorrect password!!!",
              icon: "error",
              buttonsStyling: false,
              confirmButtonText: "Try again!",
              customClass: {
                confirmButton: "btn fw-bold btn-light-danger",
              },
            }).then(() => {
              handleDelete(item);
            });

            return false;
          }
        },
      }).then((result) => {
        if (result.value == "success") {
          deleteAfterConfirmation(item);
        }
      });
    };

    const applyFilterAndSort = () => {
      if (filterAndSort.searchText != "") {
        filteredData.value = orgList.value.filter((org) => {
          if (
            org.name
              .toLowerCase()
              .search(filterAndSort.searchText.toLowerCase()) >= 0
          ) {
            return true;
          }
          return false;
        });
      } else {
        filteredData.value = orgList.value;
      }
      tableData.value = filteredData;
    };

    watch(filterAndSort, () => {
      applyFilterAndSort();
    });

    watchEffect(() => {
      applyFilterAndSort();
    });

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("Organisations", []);
      store.dispatch(Actions.ORG.LIST).then(() => {
        loading.value = false;
      });
    });

    return {
      tableHeader,
      tableData,
      handleEdit,
      filterAndSort,
      handleDelete,
      loading,
      icons,
    };
  },
});
</script>
