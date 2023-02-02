<template>
  <CardSection>
    <div class="d-flex justify-content-end mb-6">
      <!-- SEND METHODS FILTER SELECT-->
      <el-select
        class="w-25"
        placeholder="Select Send Method"
        v-model="sendMethodFilter"
      >
        <el-option value="ALL" label="ALL SEND METHODS">
          ALL SEND METHODS
        </el-option>
        <template v-for="method in messageSendMethods" :key="method">
          <el-option :value="method" :label="method">
            {{ method }}
          </el-option>
        </template>
      </el-select>
      <!-- SEND STATUS FILTER SELECT-->
      <el-select
        class="w-25 select-send-status"
        placeholder="Select Send Status"
        v-model="sendStatusFilter"
      >
        <el-option value="ALL" label="ALL SEND STATUS">
          ALL SEND STATUS
        </el-option>
        <template v-for="status in messageSendStatus" :key="status">
          <el-option :value="status" :label="status">
            {{ status }}
          </el-option>
        </template>
      </el-select>
    </div>
    <Datatable
      :table-header="tableHeader"
      :table-data="outgoingLogs"
      :loading="loading"
      :key="tableKey"
      :rows-per-page="10"
      :enable-items-per-page-dropdown="true"
    >
      <template v-slot:cell-created_at="{ row: item }">
        {{ moment(item.created_at).format("DD/MM/YYYY HH:mm").toString() }}
      </template>
      <template v-slot:cell-sending_user_name="{ row: item }">
        {{ item.sending_user_name }}
      </template>
      <template v-slot:cell-sending_doctor_name="{ row: item }">
        {{ item.sending_doctor_name + ", " + item.sending_doctor_provider }}
      </template>
      <template v-slot:cell-receiving_doctor_name="{ row: item }">
        {{ item.receiving_doctor_name + ", " + item.receiving_doctor_provider }}
      </template>
      <template v-slot:cell-send_method="{ row: item }">
        {{ item.send_method + ", " + item.send_status }}
      </template>
      <template v-slot:cell-patient_id="{ row: item }">
        {{ item.patient.title }} {{ item.patient.first_name }}
        {{ item.patient.last_name }} ({{
          moment(item.patient.date_of_birth).format("DD/MM/YYYY").toString()
        }})
      </template>
      <template v-slot:cell-actions="{ row: item }">
        <button
          @click="handleView(item)"
          class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
        >
          <span class="svg-icon svg-icon-3">
            <em class="fas fa-eye"></em>
          </span>
        </button>
      </template>
    </Datatable>
  </CardSection>
  <OutgoingModal />
</template>

<style lang="scss">
.select-send-status {
  margin-left: 15px !important;
}
</style>

<script lang="ts">
import { defineComponent, onMounted, computed, ref, watch } from "vue";
import { useStore } from "vuex";
import moment from "moment";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import OutgoingModal from "@/views/communication/OutgoingModal.vue";
import { Modal } from "bootstrap";
import IOutgoingMessageLog from "@/store/interfaces/IOutgoingMessageLog";
import { DocumentActions } from "@/store/enums/StoreDocumentEnums";
import messageSendMethods from "@/core/data/message-send-methods";
import messageSendStatus from "@/core/data/message-send-status";

export type Filters = {
  send_method: null | string;
  send_status: null | string;
};

export default defineComponent({
  name: "communication-outgoing",
  components: {
    Datatable,
    OutgoingModal,
  },
  setup() {
    const store = useStore();
    const outgoingLogs = computed<IOutgoingMessageLog[]>(
      () => store.getters.getOutgoingList
    );
    const loading = ref(false);
    const tableHeader = ref([
      {
        name: "Date/Time",
        key: "created_at",
        sortable: true,
      },
      {
        name: "Sent By",
        key: "sending_user_name",
        sortable: true,
      },
      {
        name: "Sending Doctor",
        key: "sending_doctor_name",
        sortable: true,
      },
      {
        name: "Receiving doctor",
        key: "receiving_doctor_name",
        sortable: true,
      },
      {
        name: "Status",
        key: "send_method",
        sortable: true,
      },
      {
        name: "Patient",
        key: "patient_id",
        sortable: true,
      },
      {
        name: "Actions",
        key: "actions",
      },
    ]);
    const sendMethodFilter = ref("ALL");
    const sendStatusFilter = ref("ALL");
    const tableKey = ref(0);

    const renderTable = () => tableKey.value++;

    onMounted(() => {
      setCurrentPageBreadcrumbs("Outgoing logs", ["Communication"]);
      loading.value = true;
      store.dispatch(Actions.OUTGOING.LIST).then(() => {
        loading.value = false;
      });
    });

    watch([sendMethodFilter, sendStatusFilter], () => {
      let filters = {} as Filters;
      filters.send_method =
        sendMethodFilter.value != "ALL" ? sendMethodFilter.value : null;
      filters.send_status =
        sendStatusFilter.value != "ALL" ? sendStatusFilter.value : null;
      Object.keys(filters).forEach((key) => {
        if (!filters[key]) delete filters[key];
      });
      if (filters) {
        store.dispatch(Actions.OUTGOING.LIST, filters).then(() => {
          loading.value = false;
        });
      } else {
        store.dispatch(Actions.OUTGOING.LIST).then(() => {
          loading.value = false;
        });
      }
    });

    watch(outgoingLogs, () => {
      outgoingLogs.value.sort(
        (a: IOutgoingMessageLog, b: IOutgoingMessageLog): number => {
          let diff = moment(a.created_at).diff(moment(b.created_at), "seconds");
          return diff < 0 ? 1 : diff == 0 ? 0 : -1;
        }
      );
      renderTable();
    });

    const handleView = (item) => {
      store.commit(Mutations.SET_OUTGOING.SELECT, item);
      const modal = new Modal(document.getElementById("modal_outgoing"));
      modal.show();
    };

    return {
      outgoingLogs,
      tableHeader,
      loading,
      handleView,
      moment,
      messageSendMethods,
      messageSendStatus,
      sendMethodFilter,
      sendStatusFilter,
      tableKey,
    };
  },
});
</script>
