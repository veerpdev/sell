<template>
  <CardSection>
    <div class="d-flex flex-row-reverse">
      <div class="form-check form-switch form-check-custom form-check-solid">
        <input
          class="form-check-input"
          type="checkbox"
          value=""
          id="flexSwitchChecked"
          checked="checked"
        />
        <label class="form-check-label" for="flexSwitchChecked">
          Show Complete
        </label>
      </div>
    </div>

    <Datatable
      :table-header="tableHeader"
      :table-data="tableData"
      :rows-per-page="20"
      :loading="loading"
      :enable-items-per-page-dropdown="true"
    >
      <template v-slot:cell-appointment="{ row: item }">
        <div class="p-4 d-flex flex-column">
          <span
            >{{ item.aus_formatted_date }}
            {{ item.formatted_appointment_time }}</span
          >
          <span>@ {{ item.clinic_details.name }}</span>

          <span>{{ item.appointment_type.name }}</span>
          <span> {{ item.specialist_name }}</span>
        </div>
      </template>

      <template v-slot:cell-actions="{ row: item }">
        <IconButton @click="updateCodes(item)" label="Update Codes" />
      </template>

      <template v-slot:cell-complete="{ row: item }">
        {{ item.codes.is_complete }}
      </template>
    </Datatable>
    <CodingModal></CodingModal>
  </CardSection>
</template>

<script>
import { defineComponent, onMounted, ref, computed, watchEffect } from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { CodingActions, CodingMutations } from "@/store/enums/StoreCodingEnums";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import CodingModal from "@/views/coding/codingModal.vue";
import { Modal } from "bootstrap";

export default defineComponent({
  name: "coding-dashboard",
  components: {
    Datatable,
    CodingModal,
  },
  setup() {
    const store = useStore();

    const tableData = ref([]);
    const aptList = computed(() => store.getters.getCodingAptList);
    const loading = ref(true);
    const tableHeader = ref([
      {
        name: "Appointment",
        key: "appointment",
        sortable: true,
      },
      {
        name: "Actions",
        key: "actions",
        sortable: true,
      },
      {
        name: "Complete",
        key: "complete",
        sortable: true,
      },
    ]);

    onMounted(() => {
      setCurrentPageBreadcrumbs("Coding");
      store.dispatch(CodingActions.LIST);
    });

    watchEffect(() => {
      tableData.value = aptList;
      loading.value = false;
    });

    const updateCodes = (appointment) => {
      store.commit(CodingMutations.SET_SELECT, appointment);
      const modal = new Modal(document.getElementById("modal_coding"));
      modal.show();
    };

    return {
      tableData,
      tableHeader,
      updateCodes,
      loading,
    };
  },
});
</script>
