<template>
  <ModalWrapper
    title="Available Appointments"
    modalId="available_time_slot_popup"
    modalRef="AppointmentListPopupModalRef"
  >
    <div class="d-flex flex-row" v-loading="loading">
      <div class="d-flex align-items-center justify-content-center">
        <svg
          @click="search('PREV')"
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
      <div class="flex">
        <div class="search-params d-flex flex-wrap gap-4">
          <el-alert
            v-if="notification"
            title="Warning!"
            :description="notification"
            type="warning"
            show-icon
          />
          <h4 class="text-nowrap" style="color: var(--el-color-info)">
            Clinic:
            <span class="text-primary">{{ clinic_name }}</span>
          </h4>
          <h4 class="text-nowrap" style="color: var(--el-color-info)">
            Specialist:
            <span class="text-primary">{{ specialist_name }}</span>
          </h4>
          <h4 class="text-nowrap" style="color: var(--el-color-info)">
            Time Requirement:
            <span class="text-primary">{{ time_requirement }}</span>
          </h4>
          <h4 class="text-nowrap" style="color: var(--el-color-info)">
            Time Frame:
            <span class="text-primary">{{
              props.searchNextApts.timeframe_count == 0
                ? "This " + props.searchNextApts.timeframe_type.replace("s", "")
                : props.searchNextApts.timeframe_count == 1
                ? "Next " + props.searchNextApts.timeframe_type.replace("s", "")
                : props.searchNextApts.timeframe_count +
                  " " +
                  props.searchNextApts.timeframe_type
            }}</span>
          </h4>
          <h4 class="text-nowrap" style="color: var(--el-color-info)">
            Appointment Type:
            <span class="text-primary">{{ appointment_type }}</span>
          </h4>
        </div>
        <div class="scroll h-500px">
          <template v-if="availableSlotsByDate">
            <div class="row justify-content-center h-100">
              <template v-if="availableSlotsByDate.length > 0">
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
                              date.date,
                              time_slot.time
                            )
                          "
                        >
                          {{ time_slot.time }}
                        </span>
                        <p
                          class="mb-1 small"
                          style="color: var(--el-text-color-secondary)"
                          v-if="clinic_name == 'Any'"
                        >
                          {{ time_slot.clinic_name }}
                        </p>
                        <p
                          class="small"
                          style="color: var(--el-color-warning)"
                          v-if="specialist_name == 'Any'"
                        >
                          {{ time_slot.specialist_name }}
                        </p>
                      </div>
                    </template>
                  </div>
                </div>
              </template>
              <template v-else>
                <div
                  class="align-items-center justify-content-center d-flex h-100"
                >
                  <h3 class="text-center">No Next available Appointments.</h3>
                </div>
              </template>
            </div>
          </template>
        </div>
      </div>
      <div class="d-flex align-items-center justify-content-center">
        <svg
          @click="search('NEXT')"
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
  </ModalWrapper>
</template>

<script>
import { defineComponent, computed, watch, ref } from "vue";
import { useStore } from "vuex";
import moment from "moment";
import { Modal } from "bootstrap";
import {
  AppointmentActions,
  AppointmentMutations,
} from "@/store/enums/StoreAppointmentEnums";

export default defineComponent({
  name: "appointment-list-popup",
  props: {
    allSpecialists: { type: Array, required: true },
    searchNextApts: { type: Object, required: true },
    aptTypeList: { type: Array, required: true },
    clinicList: { type: Array, required: true },
    aptTimeRequireList: { type: Array, required: true },
  },
  setup(props) {
    const store = useStore();
    const searchParam = ref(null);
    const isInitial = ref(false);
    const loading = ref(false);

    const clinic_name = computed(() => {
      const clinic = props.clinicList.find(
        ({ id }) => id === props.searchNextApts.clinic_id
      );

      return clinic == undefined ? "Any" : clinic.name;
    });

    const availableSlotsByDate = computed(
      () => store.getters.getAvailableAppointmentList
    );

    const specialist_name = computed(() => {
      if (props.allSpecialists.length > 0) {
        const specialist = props.allSpecialists.find(
          ({ id }) => id === props.searchNextApts.specialist_id
        );
        return specialist == undefined ? "Any" : specialist.full_name;
      }
      return null;
    });

    const time_requirement = computed(() => {
      const time_requirement = props.aptTimeRequireList.find(
        ({ id }) => id === props.searchNextApts.time_requirement
      );

      return time_requirement == undefined ? "Any" : time_requirement.title;
    });

    const appointment_type = computed(() => {
      const appointment_type = props.aptTypeList.find(
        ({ id }) => id === props.searchNextApts.appointment_type_id
      );

      return appointment_type == undefined ? "Any" : appointment_type.name;
    });

    const getClass = (date) => {
      var flag = moment(date.date, "YYYY-MM-DD").diff(
        moment(moment().format("YYYY-MM-DD"), "YYYY-MM-DD")
      );
      if (flag >= 0) return "col";
      else return "col bg-light bg-gradient";
    };

    const handleAddApt = (specialist_ids, date, startTime) => {
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
      selected_specialist.hrm_work_schedule.forEach(function (slot) {
        let $weekDay = moment(date).format("ddd").toString().toUpperCase();
        if (slot.week_day == $weekDay) $restriction = slot.restriction;
      });
      const item = {
        time_slot: [_date + "T" + startTime],
        date: _date,
        ava_specialist: available_specialists,
        selected_specialist: selected_specialist,
        restriction: $restriction,
        appointment_type: {
          id: props.searchNextApts.appointment_type_id,
          name: appointment_type.value,
        },
      };
      store.commit(AppointmentMutations.SET_BOOKING.SELECT, item);

      const modal = new Modal(document.getElementById("modal_create_apt"));
      modal.show();

      const current_modal = Modal.getInstance(
        document.getElementById("modal_available_time_slot_popup")
      );
      current_modal.hide();
    };

    const search = (param) => {
      if (param === "PREV") {
        searchParam.value.date = moment(searchParam.value.date, "DD/MM/YYYY")
          .add(-1, "weeks")
          .format("DD/MM/YYYY");
      }
      if (param === "NEXT") {
        searchParam.value.date = moment(searchParam.value.date, "DD/MM/YYYY")
          .add(1, "weeks")
          .format("DD/MM/YYYY");
      }
      handleSearch();
    };

    const handleSearch = async () => {
      loading.value = true;
      await store
        .dispatch(AppointmentActions.BOOKING.SEARCH.NEXT_APT, {
          ...searchParam.value,
        })
        .finally(() => {
          isInitial.value = false;
          setTimeout(() => {
            loading.value = false;
          }, 1000);
        });
    };

    watch(props.searchNextApts, () => {
      setTimeout(() => {
        searchParam.value = props.searchNextApts;
        !isInitial.value && handleSearch();
        isInitial.value = true;
      }, 300);
    });

    const notification = computed(() => {
      if (availableSlotsByDate.value.length > 0 && searchParam.value) {
        const searchDate = moment(searchParam.value.date, "DD/MM/YYYY").startOf(
          "isoWeek"
        );
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
      handleAddApt,
      availableSlotsByDate,
      clinic_name,
      specialist_name,
      time_requirement,
      appointment_type,
      moment,
      search,
      getClass,
      loading,
      props,
      notification,
    };
  },
});
</script>
