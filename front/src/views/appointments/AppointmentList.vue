<template>
  <CardSection>
    <div
      v-if="params.confirmation_status === 'PENDING'"
      class="d-flex justify-content-end"
    >
      <!-- DATE RANGE FILTER SELECT-->
      <el-select
        class="w-20 mb-6"
        placeholder="Select Date Range"
        v-model="dateRangeFilter"
      >
        <template v-for="type in dateRangeFilterTypes" :key="type.value">
          <el-option :value="type.value" :label="type.label">
            {{ type.label }}
          </el-option>
        </template>
      </el-select>
    </div>
    <Datatable
      :table-header="tableHeader"
      :table-data="tableData"
      :loading="loading"
      :rows-per-page="10"
      :enable-items-per-page-dropdown="true"
    >
      <template v-slot:cell-appointment_details="{ row: item }">
        {{ item.aus_formatted_date }} {{ item.formatted_appointment_time }}
      </template>
      <template v-slot:cell-patient="{ row: item }">
        {{ item.patient_name.full }} ({{ item.patient_details.contact_number }})
      </template>
      <template v-slot:cell-specialist="{ row: item }">
        {{ item.specialist_name }}
      </template>
      <template v-slot:cell-appointment="{ row: item }">
        {{ item.appointment_type.name }}
      </template>
      <template v-slot:cell-cancel_reason="{ row: item }">
        {{ item.confirmation_status_reason }}
      </template>
      <template
        v-if="
          actionConfirmTitle || actionCancelTitle || actionResendMessageTitle
        "
        v-slot:cell-actions="{ row: item }"
      >
        <button
          v-if="actionConfirmTitle"
          @click="actionConfirm(item.id, dateRangeFilter)"
          class="btn btn-bg-light btn-active-color-primary btn-sm me-1"
        >
          {{ actionConfirmTitle }}
        </button>
        <button
          v-if="actionCancelTitle"
          @click="actionCancel(item.id, dateRangeFilter)"
          class="btn btn-bg-light btn-active-color-primary btn-sm me-1"
        >
          {{ actionCancelTitle }}
        </button>
        <button
          v-if="actionResendMessageTitle"
          @click="actionResendMessage(item.id)"
          class="btn btn-bg-light btn-active-color-primary btn-sm me-1"
        >
          {{ actionResendMessageTitle }}
        </button>
      </template>
    </Datatable>
  </CardSection>
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
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import dateRangeFilterTypes from "@/core/data/date-range-filter-types";
import moment from "moment";

export default defineComponent({
  name: "admin-main",

  components: {
    Datatable,
  },
  props: {
    params: { required: true },
    actionConfirmTitle: { require: false },
    actionConfirm: { require: false },
    actionCancelTitle: { require: false },
    actionCancel: { require: false },
    actionResendMessageTitle: { require: false },
    actionResendMessage: { require: false },
  },
  setup(props) {
    const store = useStore();
    const tableHeader = ref([
      {
        name: "Appointment Details",
        key: "appointment_details",
        sortable: true,
      },
      {
        name: "Patient",
        key: "patient",
        sortable: true,
      },
      {
        name: "Specialist",
        key: "specialist",
        sortable: true,
      },
      {
        name: "Appointment",
        key: "appointment",
        sortable: true,
      },
    ]);
    const tableData = ref([]);
    const appointments = computed(() => store.getters.getAptList);
    const loading = ref(true);
    const dateRangeFilter = ref("Week");

    onMounted(() => {
      let now = moment();
      let data = {
        ...props.params,
        after_date: now.format(),
        before_date: now.endOf("week").format(),
      };
      store.dispatch(AppointmentActions.LIST, data).then(() => {
        tableData.value = appointments;
        loading.value = false;
      });

      if (
        props.actionConfirmTitle ||
        props.actionCancelTitle ||
        props.actionResendMessageTitle
      ) {
        tableHeader.value.push({
          name: "Actions",
          key: "actions",
          sortable: true,
        });
      }
    });

    watch(dateRangeFilter, () => {
      let now = moment();
      let data = {};
      switch (dateRangeFilter.value) {
        case "Today": {
          data = { ...props.params, date: now.format() };
          break;
        }
        case "All": {
          data = {
            ...props.params,
            after_date: now.format(),
          };
          break;
        }
        case "Week": {
          data = {
            ...props.params,
            after_date: now.format(),
            before_date: now.endOf("week").format(),
          };
          break;
        }
        case "Month": {
          data = {
            ...props.params,
            after_date: now.format(),
            before_date: now.endOf("month").format(),
          };
          break;
        }
        case "Fortnight": {
          data = {
            ...props.params,
            after_date: now.format(),
            before_date: now.add(1, "week").endOf("week").format(),
          };
          break;
        }
        default:
          break;
      }
      store.dispatch(AppointmentActions.LIST, data).then(() => {
        tableData.value = appointments;
        loading.value = false;
      });
    });

    watchEffect(() => {
      tableData.value = appointments;
      loading.value = false;
    });

    const hasActionsSlot = () => {
      return this.$slots["actions"];
    };

    return {
      tableHeader,
      tableData,
      hasActionsSlot,
      dateRangeFilterTypes,
      dateRangeFilter,
    };
  },
});
</script>
