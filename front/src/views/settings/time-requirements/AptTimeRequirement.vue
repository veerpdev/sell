<template>
  <SettingsButton />
  <div class="card w-75 mx-auto">
    <div class="card-header border-0 pt-6">
      <!--begin::Card title-->
      <div class="card-title col">
        <div class="alert alert-primary d-flex align-items-center p-2">
          <span class="svg-icon svg-icon-2hx svg-icon-primary me-2">
            <inline-svg src="media/icons/duotune/general/gen007.svg" />
          </span>
          <div class="d-flex flex-column">
            <span
              >These settings are used by the next available search form on the
              booking dashboard.</span
            >
          </div>
        </div>
      </div>
      <!--end::Card title-->

      <!--begin::Card toolbar-->
      <div class="card-toolbar col-12 col-sm-2">
        <!--begin::Toolbar-->
        <div
          class="d-flex justify-content-end ms-auto"
          data-kt-subscription-table-toolbar="base"
        >
          <!--begin::Add subscription-->
          <button
            type="button"
            class="btn btn-light-primary"
            data-bs-toggle="modal"
            data-bs-target="#modal_add_time_requirements"
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
        :rows-per-page="5"
        :enable-items-per-page-dropdown="false"
      >
        <template v-slot:cell-title="{ row: item }">
          {{ item.title }}
        </template>
        <template v-slot:cell-type="{ row: item }">
          {{ item.type }}
        </template>
        <template v-slot:cell-base_time="{ row: item }">
          {{ item.base_time }}
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
import CreateModal from "@/views/settings/time-requirements/CreateTimeRequirements.vue";
import EditModal from "@/views/settings/time-requirements/EditTimeRequirements.vue";
import { Modal } from "bootstrap";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import SettingsButton from "@/components/SettingsButton.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import moment from "moment";

export default defineComponent({
  name: "apt-time-requirements",

  components: {
    Datatable,
    CreateModal,
    EditModal,
    SettingsButton,
  },

  setup() {
    const store = useStore();
    const tableHeader = ref([
      {
        name: "Title",
        key: "title",
        sortable: true,
      },
      {
        name: "Type",
        key: "type",
        sortable: true,
      },
      {
        name: "Time",
        key: "base_time",
        sortable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);
    const tableData = ref([]);
    const aptTimeRequireList = computed(
      () => store.getters.getAptTimeRequireList
    );

    const handleEdit = (item) => {
      item.base_time =
        moment(new Date()).format("YYYY-MM-DD") + "T" + item.base_time;
      store.commit(Mutations.SET_APT_TIME_REQUIREMENT.SELECT, item);
      const modal = new Modal(
        document.getElementById("modal_edit_time_requirements")
      );
      modal.show();
    };

    const handleDelete = (id) => {
      store
        .dispatch(Actions.APT_TIME_REQUIREMENT.DELETE, id)
        .then(() => {
          store.dispatch(Actions.APT_TIME_REQUIREMENT.LIST);
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
      setCurrentPageBreadcrumbs("Appointment Time Requirements", ["Settings"]);
      store.dispatch(Actions.APT_TIME_REQUIREMENT.LIST);
      tableData.value = aptTimeRequireList;
    });

    watchEffect(() => {
      tableData.value = aptTimeRequireList;
    });

    return { tableHeader, tableData, handleEdit, handleDelete };
  },
});
</script>
