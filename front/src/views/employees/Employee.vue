<template>
  <CardSection>
    <template #default>
      <div class="d-flex flex-row justify-content-between">
        <div class="d-flex align-items-center position-relative my-1">
          <span class="svg-icon svg-icon-1 position-absolute ms-6">
            <InlineSVG icon="search" />
          </span>
          <input
            type="text"
            data-kt-subscription-table-filter="search"
            class="form-control form-control-solid w-250px ps-14"
            placeholder="Search Employees"
            v-model="filterAndSort.searchText"
          />
        </div>

        <IconButton
          @click="handleCreate"
          label="Add New Employee"
          :iconSRC="icons.plus"
        />
      </div>

      <Datatable
        v-if="tableData != undefined"
        :table-header="tableHeader"
        :table-data="tableData"
        :loading="loading"
        :rows-per-page="10"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-first_name="{ row: item }">
          {{ item.title }} {{ item.first_name }} {{ item.last_name }}
        </template>
        <template v-slot:cell-role_name="{ row: item }">
          {{ item.role_name }}
        </template>
        <template v-slot:cell-username="{ row: item }">
          {{ item.username }}
        </template>
        <template v-slot:cell-email="{ row: item }">
          {{ item.email }}
        </template>
        <template v-slot:cell-action="{ row: item }">
          <div class="d-flex justify-content-end gap-1">
            <IconButton
              v-if="item.role_id == 5"
              @click="handleEditProviderNumber(item)"
              :iconSRC="icons.location_pin"
              tooltip="Manage provider numbers"
            />
            <IconButton
              @click="handleUpdatePassword(item)"
              :iconSRC="icons.broken_chain"
              tooltip="Manage password"
            />
            <IconButton
              @click="handleEdit(item)"
              :iconSRC="icons.pencil"
              tooltip="Edit employee"
            />
            <IconButton
              @click="handleDelete(item)"
              :iconSRC="icons.bin"
              tooltip="Delete employee"
            />
          </div>
        </template>
      </Datatable>
    </template>
  </CardSection>

  <ProviderModal></ProviderModal>
  <EmployeePasswordModal></EmployeePasswordModal>
</template>
<script lang="ts">
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
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import icons from "@/core/data/icons";
import ProviderModal from "@/views/employees/modals/ProviderModal.vue";
import EmployeePasswordModal from "@/views/employees/modals/EmployeePasswordModal.vue";
import { Modal } from "bootstrap";
import IUserProfile from "@/store/interfaces/IUserProfile";
import IOrganization from "@/store/interfaces/IOrganization";
export default defineComponent({
  name: "employee-main",

  components: {
    Datatable,
    ProviderModal,
    EmployeePasswordModal,
  },

  setup() {
    const store = useStore();
    const router = useRouter();
    const loading = ref<boolean>(false);
    const filterAndSort = reactive({
      searchText: "",
    });
    const tableHeader = ref([
      {
        name: "Name",
        key: "first_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Username",
        key: "username",
        sortable: true,
        searchable: true,
      },
      {
        name: "Role",
        key: "role_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Email",
        key: "email",
        sortable: true,
        searchable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);
    const tableData = ref<IUserProfile[]>();
    const filteredData = ref<IUserProfile[]>();
    const list = computed<IUserProfile[]>(() => store.getters.employeeList);
    const organization = computed<IOrganization>(
      () => store.getters.userOrganization
    );

    const handleCreate = () => {
      if (organization.value.is_max_users) {
        const html =
          "<h3>You have reached your max allowed users.</h3><p>Please buy new user licenses to add more.</p><br/>";

        Swal.fire({
          html: html,
          icon: "warning",
          buttonsStyling: false,
          confirmButtonText: "Ok, got it!",
          customClass: {
            confirmButton: "btn btn-primary",
          },
        });
      } else {
        router.push({ name: "employees-create" });
      }
    };

    const handleEdit = (item) => {
      store.commit(Mutations.SET_EMPLOYEE.SELECT, item);
      router.push({ name: "employees-edit", params: { id: item.id } });
    };

    const handleEditProviderNumber = (item) => {
      store.commit(Mutations.SET_EMPLOYEE.SELECT, item);
      const modal = new Modal(
        document.getElementById("modal_employee_provider")
      );
      modal.show();
    };

    const handleUpdatePassword = (item) => {
      store.commit(Mutations.SET_EMPLOYEE.SELECT, item);
      const modal = new Modal(
        document.getElementById("modal_employee_password")
      );
      modal.show();
    };

    const deleteAfterConfirmation = (item) => {
      const html =
        '<p class="fs-2">Please type <b>' +
        item.first_name +
        "</b> to confirm</p><br/>";

      Swal.fire({
        input: "text",
        inputAttributes: {
          autocapitalize: "off",
          placeholder: "First Name",
        },
        html: html,
        icon: "info",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Delete",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light-primary",
        },
        preConfirm: async (data) => {
          if (data.toLowerCase() == item.first_name.toLowerCase()) {
            return "success";
          }

          return false;
        },
      }).then((result) => {
        if (result.value == "success") {
          store.dispatch(Actions.EMPLOYEE.DELETE, item.id).then(() => {
            store.dispatch(Actions.EMPLOYEE.LIST);
            Swal.fire({
              text: "Successfully Deleted!",
              icon: "success",
              buttonsStyling: false,
              confirmButtonText: "Ok, got it!",
              customClass: {
                confirmButton: "btn btn-primary",
              },
            }).then(() => {
              store.dispatch(Actions.EMPLOYEE.LIST);
            });
          });
        }
      });
    };

    const handleDelete = (item) => {
      deleteAfterConfirmation(item);
    };

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("Employees", []);
      store.dispatch(Actions.EMPLOYEE.LIST).then(() => {
        tableData.value = list.value;
        loading.value = false;
      });
    });

    const applyFilterAndSort = () => {
      if (filterAndSort.searchText != "") {
        filteredData.value = list.value.filter((employee) => {
          if (
            employee.full_name
              .toLowerCase()
              .search(filterAndSort.searchText.toLowerCase()) >= 0
          ) {
            return true;
          }

          if (
            employee.email
              .toLowerCase()
              .search(filterAndSort.searchText.toLowerCase()) >= 0
          ) {
            return true;
          }

          if (
            employee.username
              .toLowerCase()
              .search(filterAndSort.searchText.toLowerCase()) >= 0
          ) {
            return true;
          }

          if (
            employee.role.name
              .toLowerCase()
              .search(filterAndSort.searchText.toLowerCase()) >= 0
          ) {
            return true;
          }

          return false;
        });
      } else {
        filteredData.value = list.value;
      }
      tableData.value = filteredData.value;
    };

    watchEffect(() => {
      applyFilterAndSort();
    });

    watch(filterAndSort, () => {
      applyFilterAndSort();
    });

    return {
      tableHeader,
      tableData,
      handleEdit,
      handleDelete,
      handleCreate,
      filterAndSort,
      handleEditProviderNumber,
      handleUpdatePassword,
      icons,
      loading,
    };
  },
});
</script>
