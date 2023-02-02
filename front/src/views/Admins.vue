<template>
  <div class="card w-75 mx-auto">
    <!--begin::Alert-->
    <div class="alert alert-warning m-4 mb-0">
      <!--begin::Wrapper-->
      <div class="d-flex flex-column">
        <!--begin::Title-->
        <h4 class="mb-1 text-dark"></h4>
        <!--end::Title-->
        <!--begin::Content-->
        <span
          >Admins at this level have authority, to add, edit and disable
          Organisations. Please ensure only trusted and trained aurora team
          members are in this list</span
        >
        <!--end::Content-->
      </div>
      <!--end::Wrapper-->
    </div>
    <!--end::Alert-->
    <div class="card-header border-0 pt-6">
      <!--begin::Card title-->
      <div class="card-title"></div>

      <!--begin::Card title-->

      <!--begin::Card toolbar-->
      <div class="card-toolbar">
        <!--begin::Toolbar-->
        <div
          class="d-flex justify-content-end"
          data-kt-subscription-table-toolbar="base"
        >
          <!--begin::Add subscription-->
          <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modal_add_admin"
          >
            <span class="svg-icon svg-icon-2">
              <InlineSVG icon="plus" />
            </span>
            Add
          </button>
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
        :loading="loading"
        :rows-per-page="10"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-full_name="{ row: item }">
          {{ item.first_name }} {{ item.last_name }}
        </template>
        <template v-slot:cell-username="{ row: item }">
          {{ item.username }}
        </template>
        <template v-slot:cell-email="{ row: item }">
          {{ item.email }}
        </template>
        <template v-slot:cell-action="{ row: item }">
          <a
            href="#"
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
          >
            <span class="svg-icon svg-icon-3">
              <inline-svg src="media/icons/duotune/general/gen019.svg" />
            </span>
          </a>

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
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
          >
            <span class="svg-icon svg-icon-3">
              <InlineSVG icon="bin" />
            </span>
          </button>
        </template>
      </Datatable>
    </div>
  </div>
  <CreateModal></CreateModal>
  <EditModal></EditModal>
</template>

<script>
import { defineComponent, onMounted, ref, computed, watchEffect } from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import CreateModal from "@/components/admin/AddAdminModal.vue";
import EditModal from "@/components/admin/EditAdminModal.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { Modal } from "bootstrap";

export default defineComponent({
  name: "admin-main",

  components: {
    Datatable,
    CreateModal,
    EditModal,
  },

  setup() {
    const store = useStore();
    const tableHeader = ref([
      {
        name: "Full Name",
        key: "full_name",
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
    const tableData = ref([]);
    const adminList = computed(() => store.getters.adminList);
    const loading = ref(true);

    const handleEdit = (item) => {
      store.commit(Mutations.SET_ADMIN.SELECT, item);
      const modal = new Modal(document.getElementById("modal_edit_admin"));
      modal.show();
    };

    const handleDelete = (id) => {
      store
        .dispatch(Actions.ADMIN.DELETE, id)
        .then(() => {
          store.dispatch(Actions.ADMIN.LIST);
          Swal.fire({
            text: "Successfully Deleted!",
            icon: "success",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
              confirmButton: "btn btn-primary",
            },
          });
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    };

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("Administrators", []);
      store.dispatch(Actions.ADMIN.LIST).then(() => {
        tableData.value = adminList;
        loading.value = false;
      });
    });

    watchEffect(() => {
      tableData.value = adminList;
      loading.value = false;
    });
    return { tableHeader, tableData, handleEdit, handleDelete };
  },
});
</script>
