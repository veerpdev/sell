<template>
  <CardSection>
    <div class="w-100">
      <el-select
        class="mb-6"
        placeholder="Select Confirm State"
        v-model="recallsFilter"
      >
        <el-option value="-1" label="All State"></el-option>
        <template v-for="(state, index) in confirmStateList" :key="index">
          <el-option :value="index" :label="state"></el-option>
        </template>
      </el-select>
      <el-select
        class="mx-2 mb-6"
        placeholder="Select Send Method"
        v-model="methodFilter"
      >
        <el-option value="all" label="All Method"></el-option>
        <template v-for="(method, index) in sendRecallMethodList" :key="index">
          <el-option :value="method.key" :label="method.value"></el-option>
        </template>
      </el-select>
    </div>
    <Datatable
      :table-header="tableHeader"
      :table-data="tableData"
      :loading="loading"
      :rows-per-page="5"
      :enable-items-per-page-dropdown="true"
    >
      <template v-slot:cell-patient="{ row: recall }">
        {{ recall.summary.patient_name }}
      </template>
      <template v-slot:cell-appointment="{ row: recall }">
        {{ recall.summary.appointment_type }} @
        {{ recall.summary.appointment_clinic }}
        {{ recall.summary.appointment_date }}
      </template>
      <template v-slot:cell-reason="{ row: recall }">
        {{ recall.reason }}
      </template>
      <template v-slot:cell-logs="{ row: recall }">
        <div class="d-flex flex-column">
          <span v-for="log in recall.sent_logs" :key="log.id"
            >Sent via {{ log.sent_by }} @
            {{ moment(log.created_at).format("DD/M/YY") }}
          </span>
        </div>
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
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import moment from "moment";
import confirmStateList from "@/core/data/confirm-state";
import sendRecallMethodList, {
  sendRecallMethodKey,
} from "@/core/data/send-recall-method";

export default defineComponent({
  name: "patient-recalls",

  components: {
    Datatable,
  },
  setup() {
    const store = useStore();
    const tableHeader = ref([
      {
        name: "Patient",
        key: "patient",
        sortable: true,
      },
      {
        name: "Appointment",
        key: "appointment",
        sortable: true,
      },
      {
        name: "Reason",
        key: "reason",
        sortable: true,
      },
      {
        name: "Sent Logs",
        key: "logs",
        sortable: true,
      },
    ]);

    const tableData = ref([]);
    const filteredData = ref([]);
    const recalls = computed(() => store.getters.patientsRecallList);
    const recallsFilter = ref("0");
    const methodFilter = ref("all");
    const loading = ref(false);

    const applyFilterAndSort = () => {
      filteredData.value = recalls.value;
      if (recallsFilter.value !== "-1") {
        filteredData.value = filteredData.value.filter(
          (item) => item.confirmed === Number(recallsFilter.value)
        );
      }
      if (methodFilter.value !== "all") {
        filteredData.value = filteredData.value.filter((item) => {
          if (item.sent_logs && item.sent_logs.length > 0) {
            return item.sent_logs.some((log) => {
              return log.sent_by === sendRecallMethodKey[methodFilter.value];
            });
          } else return false;
        });
      }
      tableData.value = filteredData;
    };

    watch([recallsFilter, methodFilter], () => {
      applyFilterAndSort();
    });

    watchEffect(() => {
      applyFilterAndSort();
    });

    onMounted(() => {
      store.dispatch(PatientActions.RECALL.LIST).then(() => {
        loading.value = false;
      });

      setCurrentPageBreadcrumbs("Patient Recalls");
    });

    return {
      tableHeader,
      tableData,
      loading,
      moment,
      confirmStateList,
      recallsFilter,
      methodFilter,
      sendRecallMethodList,
      sendRecallMethodKey,
    };
  },
});
</script>
