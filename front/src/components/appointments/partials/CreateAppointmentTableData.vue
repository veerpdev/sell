<template>
  <td class="bg-white text-center p-2">
    <a
      v-if="specialistAvailable()"
      class="cursor-pointer"
      @click="handleCreateAppointment('info')"
    >
      <em :class="'fa fa-plus ' + setColor()"></em>
    </a>
  </td>
</template>
<script lang="ts">
import { Modal } from "bootstrap";
import { useStore } from "vuex";
import moment from "moment";
import { AppointmentMutations } from "@/store/enums/StoreAppointmentEnums";
import { computed } from "vue";

export default {
  props: {
    date: { required: true },
    startTime: { required: true },
    specialist: { required: true },
  },
  setup(props) {
    const store = useStore();

    const timeStr2Number = (time) => {
      return Number(time.split(":")[0] + time.split(":")[1]);
    };

    const setColor = () => {
      let timeSlot = getTimeSlot();
      let restriction = timeSlot.restriction;
      if (restriction == "PROCEDURE") return "text-danger";
      if (restriction == "CONSULTATION") return "text-primary";
      if (restriction == "NONE") return "text-success";
    };
    const _apt_date = computed(() => props.date);
    const handleCreateAppointment = () => {
      let timeSlot = getTimeSlot();
      const date = moment(_apt_date.value).format("YYYY-MM-DD").toString();
      const item = {
        time_slot: [date + "T" + props.startTime],
        date: date,
        selected_specialist: props.specialist,
        restriction: timeSlot.restriction,
      };
      store.commit(AppointmentMutations.SET_BOOKING.SELECT, item);
      store.commit(
        AppointmentMutations.SET_APT.SELECT_SPECIALIST,
        props.specialist
      );

      const modal = new Modal(document.getElementById("modal_create_apt"));
      modal.show();
    };

    const specialistAvailable = () => {
      if (!props.specialist.hrm_work_schedule) {
        return false;
      } else {
        if (getTimeSlot()) {
          return true;
        }
      }
    };

    const getTimeSlot = () => {
      let result;
      props.specialist.hrm_work_schedule.forEach(function (timeSlot) {
        let startTime = timeStr2Number(timeSlot.start_time);
        let endTime = timeStr2Number(timeSlot.end_time);
        let appointmentTime = timeStr2Number(props.startTime);
        if (startTime <= appointmentTime && appointmentTime < endTime) {
          result = timeSlot;
        }
      });
      return result;
    };

    return {
      handleCreateAppointment,
      specialistAvailable,
      setColor,
    };
  },
};
</script>
