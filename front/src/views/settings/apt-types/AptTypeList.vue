<template>
  <SettingsButton />
  <CardSection heading=" ">
    <template #header-actions>
      <button
        type="button"
        class="text-nowrap btn btn-light-primary ms-auto"
        :disabled="loading"
        @click.prevent="handleAdd"
      >
        <span class="svg-icon svg-icon-2">
          <InlineSVG icon="plus" />
        </span>
        Add
      </button>
    </template>
    <template #default>
      <Datatable
        :table-header="tableHeader"
        :table-data="aptTypes"
        :rows-per-page="20"
        :enable-items-per-page-dropdown="true"
        :key="`apt-type-table-${tableKey}`"
      >
        <template v-slot:cell-name="{ row: appointmentType }">
          <div class="d-flex align-items-center gap-1">
            <span
              class="p-3 text-white"
              :style="{
                'border-radius': '5px',
                color: 'black',
                'background-color': appointmentType.color,
              }"
              >{{ appointmentType.name }}</span
            >
          </div>
        </template>
        <template v-slot:cell-type="{ row: appointmentType }">
          {{ appointmentType.type }}
        </template>
        <template v-slot:cell-appointment_time="{ row: appointmentType }">
          <span class="text-capitalize">{{
            appointmentType.appointment_time
          }}</span>
        </template>
        <template v-slot:cell-anesthetist_required="{ row: appointmentType }">
          <BooleanDisplayIcon :boolean="appointmentType.anesthetist_required" />
        </template>
        <template
          v-slot:cell-collecting_person_required="{ row: appointmentType }"
        >
          <BooleanDisplayIcon
            :boolean="appointmentType.collecting_person_required"
          />
        </template>
        <template v-slot:cell-actions="{ row: appointmentType }">
          <span class="d-flex flex-row gap-2">
            <IconButton
              @click="handleEdit(appointmentType.id)"
              :iconSRC="icons.pencil"
              tooltip="Edit appointment type"
            />

            <IconButton
              @click="handleDelete(appointmentType.id)"
              :iconSRC="icons.bin"
              tooltip="Delete appointment type"
            />
          </span>
        </template>
      </Datatable>
    </template>
  </CardSection>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref, computed, watch } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import Swal from "sweetalert2/dist/sweetalert2.js";
import CardSection from "@/components/presets/GeneralElements/CardSection.vue";
import icons from "@/core/data/icons";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import IAppointmentType from "@/store/interfaces/IAppointmentType";
import IScheduleItem from "@/store/interfaces/IScheduleItem";
import SettingsButton from "@/components/SettingsButton.vue";
import BooleanDisplayIcon from "@/components/presets/GeneralElements/BooleanDisplayIcon.vue";
export default defineComponent({
  name: "apt-types",

  components: {
    Datatable,
    CardSection,
    SettingsButton,
    BooleanDisplayIcon,
  },

  setup() {
    const store = useStore();
    const router = useRouter();
    const tableHeader = ref([
      {
        name: "Name",
        key: "name",
        sortable: true,
      },
      {
        name: "Type",
        key: "type",
        sortable: true,
      },
      {
        name: "Appointment Length",
        key: "appointment_time",
        sortable: true,
      },
      {
        name: "Anesthetist Required",
        key: "anesthetist_required",
        sortable: true,
      },
      {
        name: "Collecting Person Required",
        key: "collecting_person_required",
        sortable: true,
      },
      {
        name: "Actions",
        key: "actions",
        sortable: true,
      },
    ]);
    const tableData = ref([]);
    const tableKey = ref<number>(0);
    const aptTypes = computed<IAppointmentType[]>(
      () => store.getters.getAptTypesList
    );
    const scheduleItems = computed<IScheduleItem[]>(
      () => store.getters.scheduleItemList
    );
    const loading = ref(false);
    const isAddPossible = ref(false);

    const handleAdd = () => {
      if (isAddPossible.value) {
        router.push({ name: "createAptType" });
      } else {
        Swal.fire({
          text: "Before an appointment type can be created the billing item should be created",
          buttonsStyling: false,
          showCancelButton: true,
          cancelButtonText: "Cancel",
          confirmButtonText: "Manage Billing Items",
          customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-light-primary",
          },
        }).then((result) => {
          if (result.isConfirmed)
            router.push({ name: "setting-billing-items" });
        });
      }
    };

    const handleEdit = (id) => {
      router.push({ name: "editAptType", params: { id: id } });
    };

    const handleDelete = (id) => {
      store
        .dispatch(AppointmentActions.APPOINTMENT_TYPES.DELETE, id)
        .then(() => {
          store.dispatch(AppointmentActions.APPOINTMENT_TYPES.LIST);
        });
    };

    watch(aptTypes, () => {
      tableKey.value++;
    });

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("Appointment Types", ["Settings"]);
      store.dispatch(AppointmentActions.APPOINTMENT_TYPES.LIST).then(() => {
        store
          .dispatch(Actions.SCHEDULE_ITEM.LIST)
          .then(() => {
            if (scheduleItems.value.length) {
              isAddPossible.value = true;
            }
          })
          .finally(() => {
            loading.value = false;
          });
      });
    });

    return {
      tableHeader,
      aptTypes,
      handleAdd,
      handleEdit,
      handleDelete,
      icons,
      loading,
      scheduleItems,
      tableKey,
    };
  },
});
</script>
