<template>
  <div class="card w-75 m-auto">
    <div class="card-header border-0 pt-6">
      <!--begin::Card title-->
      <div class="card-title">
        {{ clinic.name }}
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
          <button
            type="button"
            class="btn btn-light-primary ms-auto"
            @click="
              handleRoomEdit({ id: '-1', clinic_id: clinic.id, name: '' })
            "
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
        <template v-slot:cell-name="{ row: item }">
          {{ item.name }}
        </template>
        <template v-slot:cell-action="{ row: item }">
          <button
            @click="handleRoomEdit(item)"
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
          >
            <span class="svg-icon svg-icon-3">
              <InlineSVG icon="pencil" />
            </span>
          </button>

          <button
            @click="handleDelete(item)"
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
  <RoomModal></RoomModal>
</template>

<script>
import {
  defineComponent,
  onMounted,
  ref,
  computed,
  watchEffect,
  watch,
} from "vue";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import RoomModal from "@/components/clinics/EditRoom.vue";
import { Modal } from "bootstrap";

export default defineComponent({
  name: "clinics-room",

  components: {
    Datatable,
    RoomModal,
  },

  setup() {
    const store = useStore();
    const route = useRoute();
    let clinic = ref({
      id: -1,
      name: "",
    });
    const tableHeader = ref([
      {
        name: "Room Name",
        key: "name",
        sortable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);
    const tableData = ref([]);
    const clinicsList = computed(() => store.getters.clinicsList);
    const roomsList = computed(() => store.getters.roomsList);

    const deleteAfterConfirmation = (item) => {
      const html =
        '<p class="fs-2">Please type <b>' +
        item.name +
        "</b> to confirm deletion</p><br/>";

      Swal.fire({
        input: "text",
        inputAttributes: {
          autocapitalize: "off",
          placeholder: "Room Name",
        },
        html: html,
        icon: "info",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Delete",
        customClass: {
          confirmButton: "btn btn-danger",
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
          store
            .dispatch(Actions.CLINICS.ROOMS.DELETE, item)
            .then(() => {
              Swal.fire({
                text: "Successfully Deleted!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                store.dispatch(Actions.CLINICS.LIST);
                store.dispatch(Actions.CLINICS.ROOMS.LIST, clinic.value.id);
              });
            })
            .catch(({ response }) => {
              console.log(response.data.error);
            });
        }
      });
    };

    const handleDelete = (item) => {
      deleteAfterConfirmation(item);
    };

    const handleRoomEdit = (item) => {
      store.commit(Mutations.SET_CLINICS.SELECTROOMS, item);
      const modal = new Modal(
        document.getElementById("modal_edit_clinic_room")
      );
      modal.show();
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Rooms", []);
      store.dispatch(Actions.CLINICS.LIST);

      let id = route.params.id;
      store.dispatch(Actions.CLINICS.ROOMS.LIST, id);
    });

    watch(clinicsList, () => {
      let id = route.params.id;
      let clinics = clinicsList.value.filter((c) => c.id == id);
      if (clinics.length) {
        clinic.value = clinics[0];
        store.commit(Mutations.SET_CLINICS.SELECT, clinic.value);
      }
    });

    watchEffect(() => {
      tableData.value = roomsList;
    });
    return {
      tableHeader,
      tableData,
      handleDelete,
      handleRoomEdit,
      clinic,
    };
  },
});
</script>
