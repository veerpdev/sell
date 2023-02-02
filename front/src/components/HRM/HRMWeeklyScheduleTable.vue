<template>
  <CardSection>
    <table class="w-100">
      <caption>
        The weekly schedule for all employees
      </caption>
      <thead>
        <th>Employee Type</th>
        <th v-for="day in props.dateOptions.datesInWeek" :key="day.value">
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

          <td v-for="day in props.dateOptions.datesInWeek" :key="day.id">
            <div
              @click="handleEditTemplateTimeslots(employee, day)"
              class="d-flex flex-column rounded min-h-150px min-w-100px p-3"
              :class="canEditClass(employee, day)"
            >
              <span
                class="svg-icon absolute text-light-primary svg-icon-4 me-1"
                v-if="canEdit"
              >
                <InlineSVG icon="pencil" />
              </span>
              <template
                v-for="timeslot in validateSlots(
                  day.date,
                  employee.hrm_weekly_schedule
                )"
                :key="timeslot.id"
              >
                <span class="p-2" :class="slotBgColor(timeslot.status)">
                  <template v-if="props.selectedFilters.includes('time')">
                    {{ moment(timeslot.start_time, "hh:ss").format("hh:ss") }} -
                    {{
                      moment(timeslot.end_time, "hh:ss").format("hh:ss") + " "
                    }}
                  </template>

                  <span v-if="props.selectedFilters.includes('clinic')">
                    ({{ timeslotClinicName(timeslot) }})
                  </span>
                  <span
                    v-if="
                      props.selectedFilters.includes('anesthetist') &&
                      timeslot.restriction !== 'CONSULTATION' &&
                      employee.role_id === 5
                    "
                  >
                    ({{ anesthetistName(timeslot.anesthetist_id) }})
                  </span>
                  <span>
                    {{ timeslot.date }}
                    <template v-if="props.selectedFilters.includes('status')">
                      {{ timeslot.status }}
                    </template>
                  </span>
                </span>
              </template>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </CardSection>
  <EditModal v-if="props.canEdit"></EditModal>
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
import EditModal from "@/views/HRM/modals/EditModal.vue";

export default defineComponent({
  name: "hrm-time-schedule-table",
  components: {
    EditModal,
  },
  props: {
    selectedFilters: Array,
    employeeList: Object,
    clinicFilter: Number,
    dateOptions: { type: Object, required: true },
    leaveList: { type: Object, required: true },
    canEdit: { type: Boolean, required: true },
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
      if (!props.canEdit) {
        return;
      }
      schedule._title = "Edit Time Slot - " + day.label;
      schedule._action = "edit_weekly_time";
      schedule._submit = HRMActions.SCHEDULE_TEMPLATE.CREATE;
      schedule.clinic_id = props.clinicFilter.value;
      schedule.dateOptions = day;
      store.dispatch(HRMActions.ANESTHETIST.LIST, {
        date: day.date,
      });
      if (schedule.id) schedule._submit = HRMActions.WEEKLY_SCHEDULE.UPDATE;
      schedule._day = day.week_day;
      store.commit(HRMMutations.SCHEDULE.SET_SELECT, schedule);
      let timeslots = schedule.hrm_weekly_schedule.filter(
        (t) => t.week_day == schedule._day
      );
      if (!timeslots.length) {
        timeslots = [];
      }
      store.commit(HRMMutations.SCHEDULE.SET_TIMESLOT, timeslots);
      const modal = new Modal(
        document.getElementById("hrm_modal_edit_schedule")
      );
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

    const slotBgColor = (status) => {
      let result = null;
      if (status === "PUBLISHED") result = "bg-success";
      else if (status === "UNPUBLISHED") result = "bg-danger text-light";
      else if (status === "CANCELED") result = "bg-warning";
      else result = "";
      return result;
    };

    const canEditClass = (user, date) => {
      if (props.canEdit)
        return (
          " cursor-pointer bg-hover-primary" + checkEmployeeLeaves(user, date)
        );
      return null;
    };

    const validateSlots = (date, slots) => {
      let result = [];
      slots.map((slot) => {
        if (props.canEdit) {
          if (slot.date == date) {
            result.push(slot);
          }
        } else {
          if (slot.date == date && slot.status == "PUBLISHED") {
            result.push(slot);
          }
        }
      });
      return result;
    };

    const checkEmployeeLeaves = (user, date) => {
      if (props.leaveList.length > 0) {
        const result = props.leaveList.filter((leave) => {
          if (
            leave.user_id == user.id &&
            leave.start_date >= date.date &&
            leave.end_date <= date.date
          ) {
            return leave;
          }
        });
        return result.length > 0 ? " user-on-leave" : " bg-light-primary";
      } else {
        return " bg-light-primary";
      }
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
      slotBgColor,
      canEditClass,
      validateSlots,
    };
  },
});
</script>
<style lang="scss" scoped>
.user-on-leave {
  border: 1px solid #4f4f4c;
  background-color: #7f7a6e3b;
}
</style>
