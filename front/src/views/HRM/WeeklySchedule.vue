<template>
  <div class="hrm-filter-container">
    <div class="filter-selector">
      <p>Clinic</p>
      <el-select placeholder="Select Clinic" v-model="clinicFilter">
        <template v-for="clinic in clinics" :key="clinic.value">
          <el-option :value="clinic.id" :label="clinic.name">
            {{ clinic.name }}
          </el-option>
        </template>
      </el-select>
      <p>Employee Type</p>
      <el-select
        v-model="selectedEmployees"
        multiple
        placeholder="Select Employee"
        style="width: 240px"
      >
        <el-option
          v-for="type in employeeTypeList"
          :key="type.id"
          :label="type.name"
          :value="type.id"
        />
      </el-select>
    </div>
    <div class="filter-selector">
      <p>Display</p>
      <el-select
        v-model="selectedFilters"
        multiple
        placeholder="Select Filters"
        style="width: 240px"
      >
        <el-option
          v-for="item in filterOptions"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        />
      </el-select>
    </div>
    <div class="date-range-selector">
      <el-button type="info" @click="setDate(1)" plain>Previous Week</el-button>
      <el-date-picker
        v-model="dateRange.startDate"
        type="week"
        :editable="false"
        :clearable="false"
        :format="displayDateRange.datepicker"
        placeholder="Pick a week"
        @change="setDate(3)"
      />
      <el-button type="info" @click="setDate(2)" plain>Next Week</el-button>
    </div>
  </div>
  <div class="hrm-filter-container fill-buttons">
    <el-button
      type="warning"
      @click="isShowFillFromTemplate = true"
      plain
      :disabled="canPullFromTemplate"
      >Fill From Template
    </el-button>
    <el-button type="warning" @click="PublishAllSlots" plain
      >Mark All as Published
    </el-button>
  </div>
  <div v-loading="loading">
    <HRMTimeScheduleTable
      :selectedFilters="selectedFilters"
      :employeeList="employeeList"
      :clinicFilter="clinicFilter"
      :dateOptions="dateRange"
      :leaveList="leaveList"
      :canEdit="true"
    />
  </div>
  <FillFromTemplateModal
    :dialogVisible="isShowFillFromTemplate"
    @selectedData="processFillFromTemplate"
  />
</template>

<script>
import {
  defineComponent,
  onMounted,
  computed,
  watch,
  ref,
  onBeforeUnmount,
} from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import HRMTimeScheduleTable from "@/components/HRM/HRMWeeklyScheduleTable";
import moment from "moment";
import { HRMActions, HRMMutations } from "@/store/enums/StoreHRMEnums";
import FillFromTemplateModal from "@/views/HRM/modals/FillFromTemplateModal";
import Swal from "sweetalert2";
import { ElNotification } from "element-plus";

export default defineComponent({
  name: "hrm-weekly-schedule",
  components: {
    HRMTimeScheduleTable,
    FillFromTemplateModal,
  },

  setup() {
    const store = useStore();
    const clinicFilter = ref();
    const selectedFilters = ref(["time", "clinic"]);
    const selectedEmployees = ref([]);
    const loading = ref(false);
    const isShowFillFromTemplate = ref(false);
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
      {
        value: "status",
        label: "Status",
      },
    ]);
    const dateRange = ref({
      startDate: moment().startOf("isoWeek"),
      endDate: null,
      datesInWeek: [],
    });

    const clinics = computed(() => store.getters.clinicsList);
    const leaveList = computed(() => store.getters.hrmDataList);
    const employeeList = computed(() => {
      const allEmployees = store.getters.hrmScheduleList;
      let filteredList = [];
      if (selectedEmployees.value.length > 0) {
        allEmployees.filter((employee) => {
          selectedEmployees.value.map((role) => {
            if (employee.role_id === role) {
              filteredList.push(employee);
              return employee;
            }
          });
        });
        return filteredList;
      } else return allEmployees;
    });
    const employeeTypeList = computed(() => {
      const allEmployees = store.getters.hrmScheduleList;
      let list = [];
      allEmployees.map((employee) => {
        if (!list.includes(employee)) list.push(employee.role);
      });
      const uniqueArray = list.filter((value, index) => {
        const _value = JSON.stringify(value);
        return (
          index ===
          list.findIndex((obj) => {
            return JSON.stringify(obj) === _value;
          })
        );
      });
      return uniqueArray;
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("Weekly Schedule", ["HRM"]);
      store.dispatch(Actions.CLINICS.LIST);
      setDate(0);
    });

    onBeforeUnmount(() => {
      store.commit(HRMMutations.SCHEDULE.SET_LIST, []);
      store.commit(HRMMutations.SCHEDULE.SET_SELECT, []);
    });

    watch(clinics, () => {
      if (clinics.value.length) {
        clinicFilter.value = clinics.value[0].id;
      }
    });

    const setDate = (x) => {
      switch (x) {
        case 0:
          dateRange.value.startDate = moment().startOf("isoWeek");
          dateRange.value.endDate = moment().endOf("isoWeek");
          break;
        case 1:
          dateRange.value.startDate = moment(dateRange.value.startDate)
            .startOf("isoWeek")
            .subtract("days", 7);
          dateRange.value.endDate = moment(dateRange.value.endDate)
            .endOf("isoWeek")
            .subtract("days", 7);
          break;
        case 2:
          dateRange.value.startDate = moment(dateRange.value.startDate)
            .startOf("isoWeek")
            .add("days", 7);
          dateRange.value.endDate = moment(dateRange.value.endDate)
            .endOf("isoWeek")
            .add("days", 7);
          break;
        case 3:
          dateRange.value.startDate = moment(dateRange.value.startDate).add(
            "days",
            1
          );
          dateRange.value.endDate = moment(dateRange.value.startDate).endOf(
            "isoWeek"
          );
          break;
      }
      loading.value = true;
      store
        .dispatch(HRMActions.WEEKLY_SCHEDULE.LIST, {
          date: moment(dateRange.value.startDate).format("YYYY-MM-DD"),
        })
        .then(() => {
          setTimeout(() => {
            store
              .dispatch(HRMActions.EMPLOYEE_LEAVE.LIST, {
                date: moment(dateRange.value.startDate).format("YYYY-MM-DD"),
                status: "Approved",
              })
              .then(() => {
                loading.value = false;
              });
          }, 1000);
        });

      let day = dateRange.value.startDate;
      dateRange.value.datesInWeek = [];
      if (day !== null) {
        while (day <= dateRange.value.endDate) {
          dateRange.value.datesInWeek.push({
            label: moment(day).format("dddd (DD-MM-YYYY)"),
            date: moment(day).format("YYYY-MM-DD"),
            value: day,
            week_day: moment(day).format("ddd").toUpperCase(),
          });
          day = day.clone().add("days", 1);
        }
      }
    };

    const displayDateRange = computed(() => {
      let result = {};
      result.defaultF = moment(dateRange.value.startDate, "MM")
        .format("DD MMM")
        .toString();
      result.defaultF += " - ";
      result.defaultF += moment(dateRange.value.endDate, "MM")
        .format("DD MMM")
        .toString();
      result.datepicker = "[ ";
      result.datepicker += result.defaultF;
      result.datepicker += " ]";
      return result;
    });

    // send the request to copy data from template to hrm weekly schedule
    const processFillFromTemplate = (data) => {
      isShowFillFromTemplate.value = false;
      if (data) {
        loading.value = true;
        data.date = moment(dateRange.value.startDate)
          .format("YYYY-MM-DD")
          .toString();
        store.dispatch(HRMActions.WEEKLY_SCHEDULE.CREATE, data).then(() => {
          setTimeout(() => {
            store
              .dispatch(HRMActions.WEEKLY_SCHEDULE.LIST, {
                date: moment(dateRange.value.startDate).format("YYYY-MM-DD"),
              })
              .then(() => {
                loading.value = false;
              });
          }, 2000);
        });
      }
    };

    // Find if there unpublished shifts available in selected date range
    const findUnpublishedShifts = () => {
      let result = {
        unpublishedShiftFound: false,
        shiftCount: 0,
        orgShiftCount: 0,
        publishSlots: {
          id: 6,
          date: dateRange.value.startDate.format("YYYY-MM-DD").toString(),
          timeslots: [],
          deleteTimeslots: [],
          notifyUsers: [],
        },
      };
      employeeList.value.map((employee) => {
        employee.schedule_timeslots.map(() => ++result.orgShiftCount);
        employee.hrm_weekly_schedule.map((slot) => {
          if (slot.status == "UNPUBLISHED") {
            result.publishSlots.timeslots.push(slot);
            result.publishSlots.notifyUsers.push(slot.user_id);
            result.unpublishedShiftFound = true;
            result.shiftCount++;
          }
        });
      });
      return result;
    };

    const PublishAllSlots = () => {
      const shiftData = findUnpublishedShifts();
      let text = "You are about to publish " + shiftData.shiftCount + " Shifts";
      if (!shiftData.unpublishedShiftFound) {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Unpublished shifts couldn't be found !",
        });
        return;
      }

      if (shiftData.shiftCount > shiftData.orgShiftCount)
        text += " and it's more than shifts in template.";
      else if (shiftData.shiftCount < shiftData.orgShiftCount)
        text += " and it's less than shifts in template.";

      Swal.fire({
        icon: "warning",
        title:
          "Do you want to Publish all Shifts on " +
          displayDateRange.value.defaultF,
        text: text,
        showDenyButton: true,
        showCancelButton: true,
        width: 600,
        confirmButtonText: "Confirm",
        denyButtonText: `Cancel`,
      }).then((result) => {
        if (result.isConfirmed) {
          processApiCall(shiftData);
        }
      });

      const processApiCall = (shiftData) => {
        shiftData.publishSlots.timeslots.map(
          (slot) => (slot.status = "PUBLISHED")
        );
        if (shiftData.unpublishedShiftFound) {
          store
            .dispatch(HRMActions.WEEKLY_SCHEDULE.UPDATE, shiftData.publishSlots)
            .then(() => {
              store.dispatch(HRMActions.WEEKLY_SCHEDULE.LIST, {
                date: moment(dateRange.value.startDate).format("YYYY-MM-DD"),
              });
            });
        } else {
          ElNotification({
            title: "Error",
            message: "Unpublished shifts couldn't be found !",
            type: "error",
          });
        }
      };
    };
    const canPullFromTemplate = computed(() => {
      let result = false;
      const allEmployees = store.getters.hrmScheduleList;
      allEmployees.map((user) => {
        user.hrm_weekly_schedule.map((slot) => {
          if (slot.hrm_filled_week_id) result = true;
        });
      });
      return result;
    });
    return {
      clinics,
      clinicFilter,
      employeeList,
      filterOptions,
      selectedFilters,
      selectedEmployees,
      employeeTypeList,
      displayDateRange,
      setDate,
      dateRange,
      loading,
      isShowFillFromTemplate,
      processFillFromTemplate,
      PublishAllSlots,
      canPullFromTemplate,
      leaveList,
    };
  },
});
</script>
<style lang="scss">
.date-range-selector {
  display: flex;
  width: 811px;
  justify-content: space-around;
  align-items: center;
  padding: 10px;

  .date-caption {
    font-size: 14px;
    font-weight: 600;
  }
}

.example-showcase .el-loading-mask {
  z-index: 9;
}

.el-loading-spinner {
  position: sticky;
}

.fill-buttons {
  justify-content: end;
  margin-top: 0;
}
</style>
