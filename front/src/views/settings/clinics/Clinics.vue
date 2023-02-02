<template>
  <SettingsButton />
  <div class="card w-75 m-auto">
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
          <IconButton
            @click="handleCreate"
            label="Add New Clinic"
            :iconSRC="icons.plus"
          />
          <!--end::Add subscription-->
        </div>
        <!--end::Toolbar-->
      </div>
      <!--end::Card toolbar-->
    </div>
    <div class="card-body pt-0">
      <Datatable
        v-if="tableData"
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="5"
        :enable-items-per-page-dropdown="false"
      >
        <template v-slot:cell-name="{ row: item }">
          {{ item.name }}
        </template>
        <template v-slot:cell-email="{ row: item }">
          {{ item.email }}
        </template>
        <template v-slot:cell-number="{ row: item }">
          {{ item.phone_number }}
        </template>
        <template v-slot:cell-action="{ row: item }">
          <div class="d-flex gap-1">
            <IconButton
              @click="handleRoomEdit(item)"
              :iconSRC="icons.broken_chain"
              tooltip="Manage rooms"
            />
            <IconButton
              @click="handleEdit(item)"
              :iconSRC="icons.pencil"
              tooltip="Edit clinic"
            />
            <IconButton
              @click="handleDelete(item)"
              :iconSRC="icons.bin"
              tooltip="Delete clinic"
            />
          </div>
        </template>
      </Datatable>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref, computed, watch } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import IClinic from "@/store/interfaces/IClinic";
import icons from "@/core/data/icons";
import SettingsButton from "@/components/SettingsButton.vue";
export default defineComponent({
  name: "clinics-main",
  components: {
    Datatable,
    SettingsButton,
  },
  setup() {
    const store = useStore();
    const tableHeader = ref([
      {
        name: "Clinic Name",
        key: "name",
        sortable: true,
      },
      {
        name: "Email Address",
        key: "email",
        sortable: true,
      },
      {
        name: "Contact Number",
        key: "number",
        sortable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);
    const tableData = ref<IClinic[]>();
    const router = useRouter();
    const clinicsList = computed<IClinic[]>(() => store.getters.clinicsList);
    const userOrganization = computed(() => store.getters.userOrganization);

    const handleEdit = (item) => {
      store.commit(Mutations.SET_CLINICS.SELECT, item);
      router.push({ name: "clinic-edit", params: { id: item.id } });
    };

    const deleteAfterConfirmation = (item) => {
      const html =
        '<p class="fs-2">Please type <b>' +
        item.name +
        "</b> to confirm deletion</p><br/>";

      Swal.fire({
        input: "text",
        inputAttributes: {
          autocapitalize: "off",
          placeholder: "Clinic Name",
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
          store.dispatch(Actions.CLINICS.DELETE, item.id).then(() => {
            store.dispatch(Actions.CLINICS.LIST);
          });
        }
      });
    };

    const handleDelete = (item) => {
      deleteAfterConfirmation(item);
    };

    const handleRoomEdit = (item) => {
      store.commit(Mutations.SET_CLINICS.SELECT, item);
      router.push({ name: "clinic-rooms", params: { id: item.id } });
    };

    const handleCreate = () => {
      console.log(userOrganization);
      if (userOrganization.value.is_max_clinics) {
        const html =
          "<h3>You have reached your max allowed clinic.</h3><p>Please buy new clinic licenses to add more.</p><br/>";

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
        router.push({ name: "clinic-create" });
      }
    };
    onMounted(() => {
      setCurrentPageBreadcrumbs("Clinics", []);
      store.dispatch(Actions.CLINICS.LIST);
    });

    watch(clinicsList, () => {
      tableData.value = clinicsList.value;
    });

    return {
      tableHeader,
      tableData,
      handleCreate,
      handleEdit,
      handleDelete,
      handleRoomEdit,
      icons,
    };
  },
});
</script>
