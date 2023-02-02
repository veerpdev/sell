<template>
  <el-alert
    v-if="notification"
    title="Warning!"
    :description="notification"
    type="warning"
    show-icon
  />
  <div class="timeslot-table mt-5 d-flex" v-loading="loading">
    <div class="arrow left d-flex align-items-center">
      <svg
        @click="moveDate(-1)"
        xmlns="http://www.w3.org/2000/svg"
        width="16"
        height="16"
        fill="currentColor"
        class="bi bi-chevron-compact-left cursor-pointer"
        viewBox="0 0 16 16"
        style="width: 32px; height: 32px"
      >
        <path
          fill-rule="evenodd"
          d="M9.224 1.553a.5.5 0 0 1 .223.67L6.56 8l2.888 5.776a.5.5 0 1 1-.894.448l-3-6a.5.5 0 0 1 0-.448l3-6a.5.5 0 0 1 .67-.223z"
        />
      </svg>
    </div>
    <div class="table-content scroll h-500px">
      <template v-if="availableSlotsByDate.length > 0">
        <div class="row justify-content-center">
          <div
            :class="getClass(date)"
            v-for="date in availableSlotsByDate"
            :key="date"
          >
            <h3
              class="py-3 position-fixed border-bottom border-bottom-dashed border-bottom-primary"
              style="
                background: white;
                border-bottom: solid black 2px;
                padding-right: 50px;
                text-align: center;
              "
            >
              {{ date.day }} <br />{{ moment(date.date).format("DD/MM") }}
            </h3>
            <div class="mt-20">
              <template
                v-for="time_slot in date.available_timeslots"
                :key="time_slot"
              >
                <div
                  class="mt-3 justify-content-center align-items-center mw-250 text-wrap"
                >
                  <span
                    class="w-100 h-100 fw-bold d-block cursor-pointer fs-3 mb-1"
                    style="color: var(--el-color-primary)"
                    data-kt-drawer-toggle="true"
                    data-kt-drawer-target="#kt_drawer_chat"
                    @click="
                      handleAddApt(
                        time_slot.specialist_id,
                        time_slot.clinic_id,
                        date.date,
                        time_slot.time,
                        time_slot.specialist_name,
                        time_slot.clinic_name
                      )
                    "
                  >
                    {{ time_slot.time }}
                  </span>
                  <p
                    class="mb-1 small"
                    style="color: var(--el-text-color-secondary)"
                    v-if="search_params.clinic_id == ''"
                  >
                    {{ time_slot.clinic_name }}
                  </p>
                  <p
                    class="small"
                    style="color: var(--el-color-warning)"
                    v-if="search_params.specialist_id == ''"
                  >
                    {{ time_slot.specialist_name }}
                  </p>
                </div>
              </template>
            </div>
          </div>
        </div>
      </template>
      <template v-else>
        <div class="align-items-center justify-content-center d-flex h-100">
          <h3 class="text-center">No Next available Appointments.</h3>
        </div>
      </template>
    </div>
    <div class="arrow right d-flex align-items-center">
      <svg
        @click="moveDate(1)"
        xmlns="http://www.w3.org/2000/svg"
        width="16"
        height="16"
        fill="currentColor"
        class="bi bi-chevron-compact-right cursor-pointer"
        viewBox="0 0 16 16"
        style="width: 32px; height: 32px"
      >
        <path
          fill-rule="evenodd"
          d="M6.776 1.553a.5.5 0 0 1 .671.223l3 6a.5.5 0 0 1 0 .448l-3 6a.5.5 0 1 1-.894-.448L9.44 8 6.553 2.224a.5.5 0 0 1 .223-.671z"
        />
      </svg>
    </div>
  </div>
</template>

<style lang="scss">
.timeslot-table {
  font-size: 16px;
  width: 100%;
  .table-content {
    overflow-x: hidden;
    width: calc(100%);
    background-color: aliceblue;
    .table-header {
      background-color: white;
      padding-bottom: 10px;
    }
    .table-body {
      .table-cell {
        color: blue;
      }
    }
  }
  .arrow {
    width: 25px;
    svg {
      cursor: pointer;
    }
  }
}
</style>

<script>
import { defineComponent, computed, ref, onMounted, watch } from "vue";
import moment from "moment";
import { useStore } from "vuex";
import {
  AppointmentActions,
  AppointmentMutations,
} from "@/store/enums/StoreAppointmentEnums";

export default defineComponent({
  name: "weekly-timeslots-apt-table",
  components: {},
  props: {
    search: { type: Object, required: true },
    aptData: { type: Object, required: true },
    allSpecialists: { type: Object, required: true },
  },
  setup(props) {
    const store = useStore();
    const loading = ref(false);
    const search_params = ref(false);
    const availableSlotsByDate = computed(
      () => store.getters.getAvailableAppointmentList
    );
    const tableData = ref({
      cur_week: 0,
      cur_date: moment(),
      header: [],
    });
    var weeks = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];

    onMounted(() => {
      search_params.value = props.search;
      tableData.value.cur_week = 0;
      tableData.value.cur_date = moment().add(
        props.search.timeframe_count,
        props.search.timeframe_type
      );
      moveDate(0);
    });

    const moveDate = async (dir) => {
      loading.value = true;
      tableData.value.cur_week += dir;
      var start_date = new Date(
        tableData.value.cur_date.add(tableData.value.cur_week, "weeks")
      );
      let header = [];
      for (var i = 0; i < 7; i++) {
        let date = new Date(moment(start_date).add(i, "days"));
        let data = {
          label: weeks[date.getDay()],
          date: moment(date).format("DD/MM"),
        };
        header.push(data);
      }
      tableData.value.header = header;

      search_params.value.date = moment(start_date).format("DD/MM/YYYY");
      await store
        .dispatch(AppointmentActions.BOOKING.SEARCH.NEXT_APT, {
          ...search_params.value,
        })
        .finally(() => {
          setTimeout(() => {
            loading.value = false;
          }, 1000);
        });
    };

    const getClass = (date) => {
      var flag = moment(date.date, "YYYY-MM-DD").diff(
        moment(moment().format("YYYY-MM-DD"), "YYYY-MM-DD")
      );
      if (flag >= 0) return "col";
      else return "col bg-light bg-gradient";
    };

    const handleAddApt = (
      specialist_ids,
      clinic_id,
      date,
      startTime,
      specialist_name,
      clinic_name
    ) => {
      let apt = { ...props.aptData };

      const _date = moment(date).format("YYYY-MM-DD").toString();
      let available_specialists = [];
      for (let specialist of props.allSpecialists) {
        if (Object.values(specialist_ids).includes(specialist.id)) {
          let temp_specialist = Object.assign({}, specialist);

          temp_specialist.anesthetist = {
            id: temp_specialist.anesthetist_id,
            name: temp_specialist.anesthetist_name,
          };

          let dayOfWeek = moment(date).format("dddd").toString();

          dayOfWeek = dayOfWeek.toLowerCase();

          const work_hours = JSON.parse(temp_specialist.work_hours);

          temp_specialist.work_hours = work_hours[dayOfWeek];

          available_specialists.push(temp_specialist);
        }
      }
      const selected_specialist = props.allSpecialists.find(
        ({ id }) => id == specialist_ids
      );
      let $restriction;
      selected_specialist.hrm_weekly_schedule.forEach(function (slot) {
        let $weekDay = moment(date).format("ddd").toString().toUpperCase();
        if (slot.week_day == $weekDay) $restriction = slot.restriction;
      });

      const diff =
        moment(apt.date + "T" + apt.end_time).unix() -
        moment(apt.date + "T" + apt.start_time).unix();

      const endTime = moment(_date + "T" + startTime)
        .add(diff, "seconds")
        .format("HH:mm");
      const item = {
        time_slot: [_date + "T" + startTime, _date + "T" + endTime],
        date: _date,
        ava_specialist: available_specialists,
        selected_specialist: selected_specialist,
        restriction: $restriction,
        appointment_type: {
          id: props.search.appointment_type_id,
          name: apt.appointment_type_name,
        },
        specialist_id: specialist_ids,
        clinic_id: clinic_id,
        specialist_name: specialist_name,
        clinic_name: clinic_name,
      };
      store.commit(AppointmentMutations.SET_BOOKING.SELECT, item);

      apt.step = 2;
      store.commit(AppointmentMutations.SET_APT.SELECT, apt);
    };

    const notification = computed(() => {
      if (availableSlotsByDate.value.length > 0 && search_params.value.date) {
        const searchDate = moment(
          search_params.value.date,
          "DD/MM/YYYY"
        ).startOf("isoWeek");
        const resultFirstDate = moment(availableSlotsByDate.value[0].date);
        const resultEndDate = moment(availableSlotsByDate.value[6].date);
        if (!searchDate.isSame(resultFirstDate)) {
          return (
            "There are no appointments available in the selected week, the next available appointment is between " +
            resultFirstDate.format("DD/MM/YYYY") +
            " and " +
            resultEndDate.format("DD/MM/YYYY")
          );
        }
      }
      return null;
    });

    return {
      props,
      loading,
      tableData,
      moveDate,
      availableSlotsByDate,
      getClass,
      moment,
      handleAddApt,
      search_params,
      notification,
    };
  },
});
</script>
