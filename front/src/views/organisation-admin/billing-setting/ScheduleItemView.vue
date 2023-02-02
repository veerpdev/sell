<template>
  <SettingsButton />
  <div class="card w-75 m-auto">
    <div class="card-header border-0 pt-6">
      <!--begin::Card title-->
      <div class="card-title">Billing Items</div>
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
            @click="handleAddMbsItem"
          >
            <span class="svg-icon svg-icon-2">
              <InlineSVG icon="plus" />
            </span>
            Add MBS Item
          </button>

          <button
            type="button"
            class="btn btn-light-primary ms-4"
            @click="handleAddCustomItem"
          >
            <span class="svg-icon svg-icon-2">
              <InlineSVG icon="plus" />
            </span>
            Add Custom Item
          </button>
          <!--end::Add subscription-->
        </div>
        <!--end::Toolbar-->
      </div>
      <!--end::Card toolbar-->
    </div>
    <div class="card-body pt-0">
      <Datatable
        v-if="healthFundsList.length > 0 && tableHeader !== null"
        :table-header="tableHeader"
        :table-data="tableData"
        :loading="loading"
        :enable-items-per-page-dropdown="false"
        :key="`schedule-fee-table-${tableKey}`"
      >
        <template
          v-bind:key="column.key"
          v-for="column in tableHeader"
          v-slot:[cellName(column.key)]="{ row: item }"
        >
          <div
            v-if="column.key == 'mbs_item_code'"
            class="d-flex justify-content-between"
            style="max-width: 400px"
          >
            <button
              @click="handleEditItem(item)"
              role="button"
              class="text-truncate fw-bold bg-transparent border-0"
            >
              {{ getItemName(item) }}
            </button>
          </div>

          <div
            v-if="column.key == 'out_of_pocket'"
            class="text-muted fw-bold text-center"
          >
            {{ convertToCurrency(item.amount / 100) }}
          </div>

          <div
            v-if="column.key == 'actions'"
            class="text-muted fw-bold text-center"
          >
            <button
              type="button"
              class="btn btn-light-primary me-2"
              @click="handleAddScheduleFee(item)"
            >
              <span class="svg-icon svg-icon-2">
                <InlineSVG icon="plus" />
              </span>
              Add Fee
            </button>

            <button
              type="button"
              class="btn btn-light-primary px-3"
              @click="handleEditItem(item)"
            >
              <span class="svg-icon svg-icon-2 m-0 p-0">
                <InlineSVG icon="pencil" />
              </span>
            </button>
          </div>

          <button
            v-if="
              column.key != 'mbs_item_code' &&
              column.key != 'out_of_pocket' &&
              column.key != 'actions'
            "
            role="button"
            @click="handleEditFee(item.id, column.key, item[column.key])"
            class="text-primary fw-bold bg-transparent border-0 w-100 d-flex align-items-center justify-content-center edit-item"
          >
            <p class="m-0 p-0 ps-6">
              {{
                convertToCurrency(
                  (item[column.key] == null ? item.amount : item[column.key]) /
                    100
                )
              }}
            </p>

            <span class="svg-icon svg-icon-2 m-0 ms-2 p-0">
              <InlineSVG icon="pencil" />
            </span>
          </button>
        </template>
      </Datatable>
    </div>
  </div>

  <ScheduleFeeModal
    :fee="selectedFee"
    v-on:closeModal="handleCloseScheduleFeeModal"
  />

  <ScheduleItemModal
    :item="selectedItem"
    v-on:closeModal="handleCloseScheduleItemModal"
  />
</template>

<style>
th > div {
  text-align: center;
}

.edit-item > span {
  visibility: hidden;
}

.edit-item:hover > span {
  visibility: visible;
}
</style>

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
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import ScheduleFeeModal from "@/views/organisation-admin/billing-setting/ScheduleFeeModal.vue";
import ScheduleItemModal from "@/views/organisation-admin/billing-setting/ScheduleItemModal.vue";
import { convertToCurrency } from "@/core/data/billing";
import { Modal } from "bootstrap";
import SettingsButton from "@/components/SettingsButton.vue";
export default defineComponent({
  name: "schedule-fee",

  components: {
    Datatable,
    ScheduleFeeModal,
    ScheduleItemModal,
    SettingsButton,
  },
  methods: {
    cellName(name) {
      return "cell-" + name;
    },
  },
  setup() {
    const store = useStore();
    const tableHeader = ref(null);
    const tableData = ref([]);
    const filterData = ref([]);

    const selectedItem = ref();
    const selectedFee = ref();
    const scheduleItemModal = ref();
    const scheduleFeeModal = ref();

    const healthFundsList = computed(() => store.getters.healthFundsList);
    const scheduleItemList = computed(() => store.getters.scheduleItemList);
    const loading = ref(false);
    const tableKey = ref(1);

    const usedHealthFunds = ref([]);

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("Schedule Fees", ["Settings"]);
      store.dispatch(Actions.HEALTH_FUND.LIST).then(() => {
        store.dispatch(Actions.SCHEDULE_ITEM.LIST).then(() => {
          store.dispatch(Actions.MBS.LIST).then(() => {
            loading.value = false;
          });
        });
      });
    });

    watch(usedHealthFunds, () => {
      let list = [
        {
          code: "mbs_item_code",
          key: "mbs_item_code",
          name: "Item",
          sortable: true,
        },
        {
          code: "out_of_pocket",
          key: "out_of_pocket",
          name: "Out of pocket",
          sortable: false,
        },
      ];

      usedHealthFunds.value.map((h) => {
        list.push({
          code: h.code,
          key: h.code,
          name: h.name,
          sortable: false,
        });
      });

      list.push({
        code: "actions",
        key: "actions",
        name: "Actions",
        sortable: false,
      });

      tableHeader.value = list;
    });

    watch(scheduleItemList, () => {
      loading.value = true;
      filterData.value = [];

      scheduleItemList.value.map((item) => {
        let row = {
          ...item,
        };
        let cols = item.schedule_fees;
        if (cols.length) {
          cols.map((col) => {
            row[col.health_fund_code] = col.amount;
          });
        }
        filterData.value.push(row);
      });
      tableData.value = filterData.value;
      loading.value = false;
      tableKey.value++;

      // Update list of used health funds
      let funds = [];
      scheduleItemList.value.forEach((item) => {
        item.schedule_fees.forEach((fee) => {
          if (!funds.includes(fee.health_fund_code)) {
            const fund = healthFundsList.value.find(
              (fund) => fund.code == fee.health_fund_code
            );

            funds.push({
              name: fund.name,
              code: fee.health_fund_code,
            });
          }
        });
      });

      usedHealthFunds.value = funds;
    });

    watchEffect(() => {
      tableData.value = filterData.value;
    });

    const getItemName = (item) => {
      const isMbs = item.mbs_item_code ? true : false;

      if (isMbs) {
        return `${item.mbs_item_code} - ${item.name}`;
      }

      let name = [];
      if (item.internal_code) {
        name.push(item.internal_code);
      }

      name.push(item.name);

      return name.join(" - ");
    };

    const handleAddScheduleFee = (item) => {
      selectedFee.value = {
        health_fund_code: "",
        amount: 0,
        schedule_item_id: item.id,
        mode: "add",
      };

      if (!scheduleFeeModal.value) {
        scheduleFeeModal.value = new Modal(
          document.getElementById("modal_schedule_fee")
        );
      }
      scheduleFeeModal.value.show();
    };

    const handleAddMbsItem = () => {
      selectedItem.value = {
        name: "",
        description: "",
        amount: 0,
        mbs_item_code: "",
        type: "mbs",
        mode: "add",
      };

      if (!scheduleItemModal.value) {
        scheduleItemModal.value = new Modal(
          document.getElementById("modal_schedule_item")
        );
      }
      scheduleItemModal.value.show();
    };

    const handleAddCustomItem = () => {
      selectedItem.value = {
        name: "",
        description: "",
        amount: 0,
        internal_code: "",
        type: "custom",
        mode: "add",
      };

      if (!scheduleItemModal.value) {
        scheduleItemModal.value = new Modal(
          document.getElementById("modal_schedule_item")
        );
      }
      scheduleItemModal.value.show();
    };

    const handleEditItem = (item) => {
      const type =
        item.mbs_item_code !== null && item.mbs_item_code.length > 0
          ? "mbs"
          : "custom";
      selectedItem.value = {
        name: item.name,
        description: item.description,
        amount: item.amount,
        internal_code: item.internal_code,
        mbs_item_code: item.mbs_item_code,
        id: item.id,
        type: type,
        mode: "edit",
      };

      if (!scheduleItemModal.value) {
        scheduleItemModal.value = new Modal(
          document.getElementById("modal_schedule_item")
        );
      }
      scheduleItemModal.value.show();
    };

    const handleCloseScheduleItemModal = () => {
      scheduleItemModal.value.hide();
      selectedItem.value = null;
    };

    const handleEditFee = (schedule_item_id, health_fund_code, amount) => {
      const scheduleItem = scheduleItemList.value.find(
        (item) => item.id == schedule_item_id
      );
      const schedule_fee = scheduleItem.schedule_fees.find(
        (fee) => fee.health_fund_code == health_fund_code
      );

      let item = {
        schedule_item_id,
        health_fund_code,
        amount: amount ?? 0,
        mode: schedule_fee ? "edit" : "add",
      };

      if (schedule_fee) {
        item.id = schedule_fee.id;
      }

      selectedFee.value = item;

      if (!scheduleFeeModal.value) {
        scheduleFeeModal.value = new Modal(
          document.getElementById("modal_schedule_fee")
        );
      }
      scheduleFeeModal.value.show();
    };

    const handleCloseScheduleFeeModal = () => {
      scheduleFeeModal.value.hide();
      selectedFee.value = null;
    };

    const handleDelete = (id) => {
      loading.value = true;

      Swal.fire({
        html: "Are you sure you want to remove this item? All custom fees will be lost.",
        icon: "warning",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Confirm",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light-primary",
        },
        preConfirm: async () => {
          store.dispatch(Actions.SCHEDULE_ITEM.DELETE, id).then(() => {
            store.dispatch(Actions.SCHEDULE_ITEM.LIST);
          });
          loading.value = false;
          return true;
        },
      });
    };

    return {
      tableHeader,
      tableData,
      loading,
      handleAddMbsItem,
      handleAddCustomItem,
      handleCloseScheduleItemModal,
      handleEditFee,
      handleEditItem,
      handleDelete,
      selectedItem,
      selectedFee,
      healthFundsList,
      tableKey,
      convertToCurrency,
      handleCloseScheduleFeeModal,
      getItemName,
      handleAddScheduleFee,
    };
  },
});
</script>
