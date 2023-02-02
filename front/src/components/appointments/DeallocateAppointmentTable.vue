<template>
  <!--begin::Stepper-->
  <div class="mx-auto w-100">
    <!--begin::Content-->
    <div class="d-flex flex-row-fluid flex-center bg-white rounded">
      <div class="w-100 py-20 px-9">
        <div class="d-flex flex-row justify-content-between">
          <HeadingText :text="title" />
        </div>
        <el-divider border-style="double" />
        <Datatable
          :table-header="header"
          :table-data="filteredTableData"
          :loading="loading"
          :rows-per-page="10"
          :enable-items-per-page-dropdown="true"
        >
          <template v-slot:cell-date="{ row: item }">
            {{ item.aus_formatted_date }}
          </template>
          <template v-slot:cell-appointment_type_name="{ row: item }">
            {{ item.appointment_type_name }}
          </template>

          <template v-slot:cell-specialist_name="{ row: item }">
            {{ item.specialist_name }}
          </template>
          <template v-slot:cell-anesthetist_name="{ row: item }">
            {{ item.anesthetist_name }}
          </template>

          <template v-slot:cell-specialist_reason="{ row: item }">
            {{ filterCancelReason(item, "specialist") }}
          </template>

          <template v-slot:cell-anesthetist_reason="{ row: item }">
            {{ filterCancelReason(item, "anesthetist") }}
          </template>

          <template v-slot:cell-confirmation_status="{ row: item }">
            <el-tag
              :type="tagClass(item.confirmation_status)"
              disable-transitions
              >{{ item.confirmation_status }}
            </el-tag>
          </template>

          <template v-slot:cell-start_time="{ row: item }">
            {{ item.start_time }}
          </template>

          <template v-slot:cell-clinic="{ row: item }">
            {{ item.clinic.name }}
          </template>

          <template v-slot:cell-patient_name="{ row: item }">
            {{ item.patient_name.full }}
          </template>
          <template v-slot:cell-action="{ row: item }">
            <div class="d-flex justify-content-start">
              <button
                @click="handleMove(item)"
                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
              >
                <span class="svg-icon svg-icon-3">
                  <InlineSVG icon="pencil" />
                </span>
              </button>
            </div>
          </template>
        </Datatable>
      </div>
    </div>
  </div>
  <MoveModal :isDisableAptTypeList="true" />
</template>
<script lang="ts">
import { defineComponent, PropType, ref, watch } from "vue";
import { useStore } from "vuex";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import { Modal } from "bootstrap";
import { AppointmentMutations } from "@/store/enums/StoreAppointmentEnums";
import MoveModal from "@/components/appointments/AppointmentMoveModal.vue";

export default defineComponent({
  name: "deallocate-appointments-table",
  props: {
    tableData: { type: Array as PropType<Array<unknown>> },
    header: { type: Object },
    title: { type: String },
    loading: { type: Boolean },
  },
  components: { Datatable, MoveModal },
  setup(props) {
    const store = useStore();
    const filteredTableData = ref<Array<unknown>>([]);

    watch(props, () => {
      if (props.tableData && props.tableData?.length > 0) {
        filteredTableData.value = props.tableData.map((data) => data);
      }
    });

    const tagClass = (data) => {
      if (data == "PENDING") return "";
      else if (data == "CONFIRMED") return "success";
      else if (data == "CANCELED") return "danger";
      else if (data == "MISSED") return "warning";
    };

    const filterCancelReason = (item, type) => {
      if (type == "specialist") {
        const data = item.specialist.employee_leaves.filter((leave) => {
          return leave.start_date <= item.date && leave.end_date >= item.date;
        });
        return data[0].leave_type;
      }
      if (type == "anesthetist") {
        const data = item.anesthetist.employee_leaves.filter((leave) => {
          return leave.start_date <= item.date && leave.end_date >= item.date;
        });
        return data[0].leave_type;
      }
    };

    const handleMove = (item) => {
      item.step = 0;
      item.action = "move";
      store.commit(AppointmentMutations.SET_APT.SELECT, item);
      const modal = new Modal(document.getElementById("modal_move_apt"));
      modal.show();
    };

    return {
      handleMove,
      tagClass,
      props,
      filterCancelReason,
      filteredTableData,
    };
  },
});
</script>
