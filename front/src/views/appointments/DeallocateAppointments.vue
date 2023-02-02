<template>
  <div class="mx-auto w-100">
    <div class="d-flex flex-row-fluid flex-start bg-white rounded my-4 p-4">
      <div class="d-flex flex-row justify-content-between p-4">
        <HeadingText class="px-4 m-0" text="Search Appointments" />
        <el-date-picker
          class="ml-4"
          v-model="selectedDate"
          type="daterange"
          range-separator="To"
          start-placeholder="Start date"
          end-placeholder="End date"
          :clearable="false"
          :editable="false"
          size="large"
          @change="updateTable"
        />
      </div>
      <div class="d-flex flex-row w-100 justify-content-end p-4">
        <button
          class="btn btn-primary w-25"
          type="button"
          @click="handleBulkMove"
        >
          Bulk Move Appointments
        </button>
      </div>
    </div>
  </div>
  <AptTable
    :tableData="specialistAptList"
    :header="tableHeader"
    :loading="loading"
    title="Deallocate Specialist Appointments"
  />
  <BulkMoveAptModal />
</template>
<script lang="ts">
import {
  computed,
  defineComponent,
  onBeforeUnmount,
  onMounted,
  ref,
} from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { Actions } from "@/store/enums/StoreEnums";
import { mask } from "vue-the-mask";
import { HRMActions, HRMMutations } from "@/store/enums/StoreHRMEnums";
import moment from "moment";
import AptTable from "@/components/appointments/DeallocateAppointmentTable.vue";
import BulkMoveAptModal from "@/components/appointments/AppointmentBulkMoveModal.vue";
import { Modal } from "bootstrap";

export default defineComponent({
  name: "deallocate-appointments",
  directives: {
    mask,
  },
  components: { BulkMoveAptModal, AptTable },
  setup() {
    const store = useStore();
    const tableHeader = ref([
      {
        name: "Date",
        key: "date",
        sortable: true,
        searchable: true,
      },
      {
        name: "Appointment Type",
        key: "appointment_type_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Specialist",
        key: "specialist_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Patient Name",
        key: "patient_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Clinic",
        key: "clinic",
        sortable: true,
        searchable: true,
      },
      {
        name: "Apt Start Tme",
        key: "start_time",
        sortable: true,
        searchable: true,
      },
      {
        name: "Reason",
        key: "specialist_reason",
        sortable: true,
        searchable: true,
      },
      {
        name: "Confirmation Status",
        key: "confirmation_status",
        sortable: true,
        searchable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);

    const tableAnesthetistHeader = ref([
      {
        name: "Date",
        key: "date",
        sortable: true,
        searchable: true,
      },
      {
        name: "Appointment Type",
        key: "appointment_type_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Anesthetist",
        key: "anesthetist_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Patient Name",
        key: "patient_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Clinic",
        key: "clinic",
        sortable: true,
        searchable: true,
      },
      {
        name: "Apt Start Tme",
        key: "start_time",
        sortable: true,
        searchable: true,
      },
      {
        name: "Reason",
        key: "anesthetist_reason",
        sortable: true,
        searchable: true,
      },
      {
        name: "Confirmation Status",
        key: "confirmation_status",
        sortable: true,
        searchable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);
    const selectedDate = ref([
      moment().startOf("isoWeek").format("YYYY-MM-DD"),
      moment().endOf("month").format("YYYY-MM-DD"),
    ]);
    const loading = ref(false);
    const specialistAptList = computed(() => {
      const aptList = store.getters.hrmDataList;
      if (!aptList.specialistAppointments) return [];
      return aptList.specialistAppointments;
    });

    const anesthetistAptList = computed(() => {
      const aptList = store.getters.hrmDataList;
      if (!aptList.anesthetistAppointments) return [];
      return aptList.anesthetistAppointments;
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("Deallocate Appointments", ["Booking"]);
      store.dispatch(Actions.CLINICS.LIST);
      store.dispatch(HRMActions.DEALLOCATE_APPOINTMENTS.LIST, {
        start_date: moment().startOf("isoWeek").format("YYYY-MM-DD"),
        end_date: moment().endOf("month").format("YYYY-MM-DD"),
      });
    });

    onBeforeUnmount(() => {
      store.commit(HRMMutations.DATA.SET_LIST, []);
    });

    const tagClass = (data) => {
      if (data == "PENDING") return "";
      else if (data == "CONFIRMED") return "success";
      else if (data == "CANCELED") return "danger";
      else if (data == "MISSED") return "warning";
    };

    const updateTable = () => {
      store.dispatch(HRMActions.DEALLOCATE_APPOINTMENTS.LIST, {
        start_date: moment(selectedDate.value[0]).format("YYYY-MM-DD"),
        end_date: moment(selectedDate.value[1]).format("YYYY-MM-DD"),
      });
    };

    const handleBulkMove = () => {
      store.dispatch(Actions.SPECIALIST.LIST);
      const modal = new Modal(document.getElementById("modal_bulk_move_apt"));
      modal.show();
    };

    return {
      tagClass,
      tableHeader,
      loading,
      specialistAptList,
      anesthetistAptList,
      tableAnesthetistHeader,
      selectedDate,
      updateTable,
      handleBulkMove,
    };
  },
});
</script>
