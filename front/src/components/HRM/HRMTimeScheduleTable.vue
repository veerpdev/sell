<template>
  <CardSection>
    <table class="w-100">
      <caption>
        The time sheet templates for all employees
      </caption>
      <thead>
        <th>Employee Type</th>
        <th v-for="day in weekdays" :key="day.value">
          {{ day.label }}
        </th>
      </thead>
      <tbody>
        <tr
          class="min-h-100px"
          v-for="employee in props.employeeList"
          :key="employee"
        >
          <td
            class="d-flex text-hover-primary cursor-pointer background-hover-light-primary flex-column"
          >
            {{ employee.full_name }}<br />
            ({{
              employeeRoles.filter((x) => x.value == employee.role_id)[0].label
            }})
          </td>

          <td v-for="day in weekdays" :key="day.id">
            <div
              @click="handleEditTemplateTimeslots(employee, day)"
              class="d-flex flex-column rounded min-h-150px min-w-100px cursor-pointer bg-hover-primary bg-light-primary p-3"
            >
              <span
                class="svg-icon absolute text-light-primary svg-icon-4 me-1"
              >
                <InlineSVG icon="pencil" />
              </span>
              <template
                v-for="timeslot in employee.schedule_timeslots.filter(
                  (x) => x.week_day == day.value
                )"
                :key="timeslot.id"
              >
                <span
                  class="p-2"
                  :class="
                    clinicFilter == timeslot.clinic_id
                      ? 'bg-primary text-light'
                      : ''
                  "
                >
                  <template v-if="props.selectedFilters.includes('time')">
                    {{ moment(timeslot.start_time, "hh:ss").format("hh:ss") }} -
                    {{ moment(timeslot.end_time, "hh:ss").format("hh:ss") }}
                  </template>

                  <span v-if="props.selectedFilters.includes('clinic')">
                    ({{ timeslotClinicName(timeslot) }})
                  </span>
                  <span
                    v-if="
                      props.selectedFilters.includes('anesthetist') &&
                      (timeslot.restriction === 'PROCEDURE' ||
                        timeslot.restriction === 'NONE') &&
                      employee.role_id === 5
                    "
                  >
                    ({{ anesthetistName(timeslot.anesthetist_id) }})
                  </span>
                </span>
              </template>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </CardSection>
  <EditModal></EditModal>
</template>
<script>
import { defineComponent, onMounted, computed, watch, ref } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { HRMActions, HRMMutations } from "@/store/enums/StoreHRMEnums";
import { Actions } from "@/store/enums/StoreEnums";
import weekdays from "@/core/data/weekdays";
import employeeRoles from "@/core/data/employee-roles";
import moment from "moment";
import { Modal } from "bootstrap";
import EditModal from "@/views/HRM/modals/EditWeeklyScheduleModal.vue";

export default defineComponent({
  name: "hrm-time-schedule-table",
  components: {
    EditModal,
  },
  props: {
    selectedFilters: Array,
    employeeList: Object,
    clinicFilter: Number,
  },
  setup(props) {
    const store = useStore();
    const selectedEmployees = ref([]);
    const filterOptions = ref([
      {
        value: "time",
        label: "Time",
      },
      {
        value: "clinic",
        label: "Clinic",
      },
      {
        value: "anesthetist",
        label: "Anesthetist",
      },
    ]);
    const clinics = computed(() => store.getters.clinicsList);

    onMounted(() => {
      setCurrentPageBreadcrumbs("Weekly Schedule Template", ["HRM"]);
      store.dispatch(Actions.CLINICS.LIST);
      store.dispatch(Actions.EMPLOYEE.LIST);
    });

    const handleEditTemplateTimeslots = (schedule, day) => {
      schedule._title = "Edit Time Slot - " + day.label;
      schedule._action = "edit_weekly_time";
      schedule._submit = HRMActions.SCHEDULE_TEMPLATE.CREATE;
      schedule.clinic_id = props.clinicFilter;
      store.dispatch(HRMActions.ANESTHETIST.LIST, {
        day: day.value,
      });
      if (schedule.id) schedule._submit = HRMActions.SCHEDULE_TEMPLATE.UPDATE;
      schedule._day = day.value;

      store.commit(HRMMutations.SCHEDULE.SET_SELECT, schedule);
      let timeslots = schedule.schedule_timeslots.filter(
        (t) => t.week_day == schedule._day
      );
      if (!timeslots.length) {
        timeslots = [];
      }
      store.commit(HRMMutations.SCHEDULE.SET_TIMESLOT, timeslots);
      const modal = new Modal(document.getElementById("modal_edit_schedule"));
      modal.show();
    };

    const timeslotClinicName = (timeslot) => {
      let clinicName = null;
      if (timeslot) {
        clinicName = clinics.value
          .filter((x) => x.id == timeslot.clinic_id)[0]
          .name.split(" ")[0];
      }
      return clinicName;
    };

    const anesthetistName = (id) => {
      let result = "Anesthetist - Not Set";
      const allEmployees = store.getters.employeeList;
      allEmployees.filter((employee) => {
        if (employee.id === id) {
          result = "Anesthetist - " + employee.first_name;
        }
      });
      return result;
    };

    return {
      weekdays,
      moment,
      handleEditTemplateTimeslots,
      clinics,
      employeeRoles,
      timeslotClinicName,
      filterOptions,
      anesthetistName,
      selectedEmployees,
      props,
    };
  },
});
</script>
