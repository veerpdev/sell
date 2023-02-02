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
  <div v-loading="loading">
    <HRMTimeScheduleTable
      :selectedFilters="selectedFilters"
      :employeeList="employeeList"
      :clinicFilter="clinicFilter"
      :dateOptions="dateRange"
      :canEdit="false"
    />
  </div>
</template>

<script>
import { defineComponent, onMounted, computed, watch, ref } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import HRMTimeScheduleTable from "@/components/HRM/HRMWeeklyScheduleTable";
import moment from "moment";
import { HRMActions } from "@/store/enums/StoreHRMEnums";
import Swal from "sweetalert2";
import { ElNotification } from "element-plus";

export default defineComponent({
  name: "my-schedule",
  components: {
    HRMTimeScheduleTable,
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
    const employeeList = computed(() => {
      const allEmployees = store.getters.hrmWeeklyTemplatesData;
      const user = store.getters.currentUser;
      let filteredList = [];
      allEmployees?.filter((employee) => {
        if (employee && employee.id === user.profile.id) {
          filteredList.push(employee);
          return employee;
        }
      });
      return filteredList;
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("My Schedule", ["HRM"]);
      store.dispatch(Actions.CLINICS.LIST);
      setDate(0);
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
          loading.value = false;
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

    const processFillFromTemplate = (data) => {
      isShowFillFromTemplate.value = false;
      if (data)
        data.date = moment(dateRange.value.startDate)
          .format("YYYY-MM-DD")
          .toString();
      store.dispatch(HRMActions.WEEKLY_SCHEDULE.CREATE, data).then(() => {
        store.dispatch(HRMActions.WEEKLY_SCHEDULE.LIST, {
          date: moment(dateRange.value.startDate).format("YYYY-MM-DD"),
        });
      });
    };

    const PublishAllSlots = () => {
      Swal.fire({
        title:
          "Do you want to Publish all Shifts on " +
          displayDateRange.value.defaultF,
        showDenyButton: true,
        showCancelButton: true,
        width: 600,
        confirmButtonText: "Confirm",
        denyButtonText: `Cancel`,
      }).then((result) => {
        if (result.isConfirmed) {
          processApiCall();
        }
      });
      const processApiCall = () => {
        let publishSlots = {
          id: 6,
          date: dateRange.value.startDate.format("YYYY-MM-DD").toString(),
          timeslots: [],
          deleteTimeslots: [],
          notifyUsers: [],
        };
        let unpublishedShiftFound = false;

        employeeList.value.map((employee) => {
          employee.hrm_weekly_schedule.map((slot) => {
            if (slot.status == "UNPUBLISHED") {
              slot.status = "PUBLISHED";
              publishSlots.timeslots.push(slot);
              publishSlots.notifyUsers.push(slot.user_id);
              unpublishedShiftFound = true;
            }
          });
        });
        if (unpublishedShiftFound) {
          store
            .dispatch(HRMActions.WEEKLY_SCHEDULE.UPDATE, publishSlots)
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

    return {
      clinics,
      clinicFilter,
      employeeList,
      filterOptions,
      selectedFilters,
      selectedEmployees,
      displayDateRange,
      setDate,
      dateRange,
      loading,
      isShowFillFromTemplate,
      processFillFromTemplate,
      PublishAllSlots,
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
