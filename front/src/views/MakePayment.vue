<template>
  <div class="card w-lg-75 m-auto">
    <div class="card-header border-0 pt-6">
      <!--begin::Card title-->
      <div class="card-title">
        <!--begin::Input-->
        <div>
          <select
            class="form-select form-select-solid select2-hidden-accessible"
            v-model="currentClinic"
            @change="handleClinic"
          >
            <option label="All Clinics" :value="0">All</option>
            <option
              v-for="clinic in clinicsList"
              :key="clinic.id"
              :label="clinic.name"
              :value="clinic.id"
            >
              {{ clinic.name }}
            </option>
          </select>
        </div>
        <!--end::Input-->
      </div>
      <!--begin::Card title-->

      <!--begin::Card toolbar-->
      <div class="card-toolbar">
        <!--begin::Toolbar-->
        <div
          class="d-flex justify-content-end"
          data-kt-subscription-table-toolbar="base"
        >
          <!--begin::Switch-->
          <label
            class="form-check form-switch form-check-custom form-check-solid"
          >
            <!--begin::Label-->
            <span
              class="form-check-label fw-bold text-muted"
              for="kt_modal_add_customer_billing"
            >
              Show Unpaid Only
            </span>
            <!--end::Label-->

            <!--begin::Input-->
            <input
              class="form-check-input ms-3"
              name="billing"
              type="checkbox"
              value="1"
              id="kt_modal_add_customer_billing"
              checked="checked"
              v-model="showAll"
              @change="handleSwitch"
            />
            <!--end::Input-->

            <!--begin::Label-->
            <span
              class="form-check-label fw-bold text-muted"
              for="kt_modal_add_customer_billing"
            >
              Show All
            </span>
            <!--end::Label-->
          </label>
          <!--end::Switch-->
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
        :key="tableKey"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-time="{ row: item }">
          {{ item.start_time }} <br />
          {{ item.date }}
        </template>
        <template v-slot:cell-patient_name="{ row: item }">
          {{ item.patient_name.full }}
        </template>
        <template v-slot:cell-attendance_status="{ row: item }">
          <span
            :class="`text-uppercase badge badge-light-${
              item.attendance_status === 'not_present'
                ? 'dark'
                : item.attendance_status === 'waiting'
                ? 'warning'
                : item.attendance_status === 'checked_in'
                ? 'success'
                : 'primary'
            }`"
          >
            {{ item.attendance_status.replace("_", " ") }}
          </span>
        </template>
        <template v-slot:cell-actions="{ row: item }">
          <!-- v-if="item.outstanding_balance > 0" -->
          <button
            class="btn btn-bg-light btn-active-color-primary btn-sm me-1"
            @click="handlePay(item)"
          >
            <span class="svg-icon svg-icon-3">
              <inline-svg src="media/icons/duotune/finance/fin002.svg" />
            </span>
            Pay
          </button>
        </template>
      </Datatable>
    </div>
  </div>
</template>

<script>
import { defineComponent, onMounted, ref, computed, watch } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import { Actions } from "@/store/enums/StoreEnums";

export default defineComponent({
  name: "make-payment-main",

  components: {
    Datatable,
  },

  setup() {
    const store = useStore();
    const router = useRouter();
    const tableHeader = ref([
      {
        name: "Time",
        key: "time",
        sortable: true,
      },
      {
        name: "Patient Name",
        key: "patient_name",
        sortable: true,
      },
      {
        name: "Attendance Status",
        key: "attendance_status",
        sortable: true,
      },
      {
        name: "Actions",
        key: "actions",
      },
    ]);
    const tableData = ref([]);
    const paymentData = ref([]);
    const list = computed(() => store.getters.paymentList);
    const clinicsList = computed(() => store.getters.clinicsList);
    const currentClinic = ref(0);
    const showAll = ref(true);
    const tableKey = ref(0);

    const renderTable = () => tableKey.value++;

    const handlePay = (item) => {
      const id = item.id;
      router.push({ name: "make-payment-pay", params: { id } });
    };

    watch(list, () => {
      paymentData.value = list.value;
      tableData.value = paymentData.value;
      renderTable();
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("Out of Pocket Payment", ["Billing"]);
      store.dispatch(Actions.MAKE_PAYMENT.LIST);
      store.dispatch(Actions.CLINICS.LIST);
    });

    return {
      tableHeader,
      tableData,
      tableKey,
      clinicsList,
      currentClinic,
      showAll,
      handlePay,
    };
  },
});
</script>
