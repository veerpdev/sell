<template>
  <div class="card w-75 mx-auto">
    <div class="card-header border-0 pt-6">
      <!--begin::Card title-->
      <div class="card-title">
        <!--begin::Search-->
        <div class="d-flex align-items-center position-relative my-1">
          <span class="svg-icon svg-icon-1 position-absolute ms-6">
            <InlineSVG icon="search" />
          </span>
          <input
            type="text"
            data-kt-subscription-table-filter="search"
            class="form-control form-control-solid w-250px ps-14"
            placeholder="Search Health Funds"
          />
        </div>
        <!--end::Search-->
      </div>
      <!--begin::Card title-->

      <!--begin::Card toolbar-->
      <div class="card-toolbar">
        <!--begin::Toolbar-->
        <div
          class="d-flex justify-content-end"
          data-kt-subscription-table-toolbar="base"
        >
          <!--begin::Export-->
          <button
            type="button"
            class="btn btn-light-primary me-3"
            data-bs-toggle="modal"
            data-bs-target="#kt_subscriptions_export_modal"
          >
            <span class="svg-icon svg-icon-2">
              <inline-svg src="media/icons/duotune/arrows/arr078.svg" />
            </span>
            Export
          </button>
          <!--end::Export-->
        </div>
        <!--end::Toolbar-->
      </div>
      <!--end::Card toolbar-->
    </div>
    <div class="card-body pt-0">
      <Datatable
        :table-header="tableHeader"
        :table-data="tableData"
        :key="tableKey"
        :rows-per-page="10"
        :enable-items-per-page-dropdown="false"
      >
        <template v-slot:cell-fundCode="{ row: item }">
          {{ item.code }}
        </template>
        <template v-slot:cell-fundName="{ row: item }">
          {{ item.name }}
        </template>
        <template v-slot:cell-onlineSubmission="{ row: item }">
          {{ item.is_online_clipse === 1 ? "Yes" : "No" }}
        </template>
        <template v-slot:cell-lastUpdate="{ row: item }">
          {{ formatDate(item.updaed_at) }}
        </template>
        <template v-slot:cell-action>
          <a
            href="#"
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
          >
            <span class="svg-icon svg-icon-3">
              <inline-svg src="media/icons/duotune/general/gen019.svg" />
            </span>
          </a>

          <a
            href="#"
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
          >
            <span class="svg-icon svg-icon-3">
              <InlineSVG icon="pencil" />
            </span>
          </a>

          <a
            href="#"
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
          >
            <span class="svg-icon svg-icon-3">
              <InlineSVG icon="bin" />
            </span>
          </a>
        </template>
      </Datatable>
    </div>
  </div>
</template>

<script>
import { defineComponent, onMounted, ref, computed, watchEffect } from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import { Actions } from "@/store/enums/StoreEnums";
import moment from "moment";

export default defineComponent({
  name: "health-fund",

  components: {
    Datatable,
  },

  setup() {
    const store = useStore();
    const tableHeader = ref([
      {
        name: "Fund Code",
        key: "fundCode",
        sortable: true,
      },
      {
        name: "Fund Name",
        key: "fundName",
        sortable: true,
      },
      {
        name: "Online Submission To Eclipse",
        key: "onlineSubmission",
        sortable: true,
      },
      {
        name: "Last Updated On",
        key: "lastUpdate",
        sortable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);
    const tableData = ref([]);
    const healthFundsList = computed(() => store.getters.healthFundsList);

    const formatDate = (data) => {
      return moment(data).format("dddd, MMMM Do YYYY");
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Health Funds", []);
      store.dispatch(Actions.HEALTH_FUND.LIST);
      tableData.value = healthFundsList;
    });

    watchEffect(() => {
      tableData.value = healthFundsList;
    });

    return { tableHeader, tableData, formatDate };
  },
});
</script>
