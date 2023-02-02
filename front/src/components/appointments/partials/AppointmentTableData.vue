<template>
  <div
    v-if="!appointment.draft_status"
    class="py-2 px-4 cursor-pointer h-100 justify-content-center"
    :style="{
      'background-color':
        appointment.confirmation_status == 'MISSED' ||
        appointment.attendance_status == 'CHECKED_OUT'
          ? '#cccccc'
          : appointment.appointment_type.color,
    }"
  >
    <div
      class="fw-bold d-flex flex-column justify-content-center align-items-center"
    >
      <span class="text-uppercase fw-bolder" v-if="userRole == 'specialist'">
        {{ appointment.appointment_type_name }}
      </span>
      <span class="text-uppercase fs-6" v-if="appointment.patient">
        {{ appointment.patient.first_name }}
        {{ appointment.patient.last_name }}
        ({{ appointment.patient.contact_number }})
        <span v-if="userRole != 'specialist'"
          >-{{ appointment.clinic.nickname_code }}
        </span>
        <span
          v-if="appointment.patient.allergies.length > 0"
          class="badge badge-light-danger opacity-50 mx-2"
          data-bs-toggle="tooltip"
          data-bs-html="true"
          :title="getTootTip(appointment.patient.allergies)"
        >
          ALLERGY
        </span>
        <span
          v-if="appointment.confirmation_status == 'MISSED'"
          class="badge badge-warning opacity-50 mx-2"
        >
          MISSED
        </span>
        <span
          v-if="appointment.attendance_status == 'CHECKED_IN'"
          class="badge badge-success"
        >
          CHECKED IN
        </span>
        <span
          v-if="appointment.attendance_status == 'CHECKED_OUT'"
          class="opacity-50 badge badge-light-dark disabled"
        >
          CHECKED OUT
        </span>
      </span>
    </div>
  </div>
  <div
    class="py-2 px-4 h-100 d-flex flex-column justify-content-center align-items-center"
    style="background-color: #5d5c5c"
    v-else-if="appointment.draft_status"
  >
    <span
      class="opacity-50 badge badge-light-dark disabled text-uppercase fw-bolder"
    >
      {{ appointment.creator_name + " is updating this spot" }}
    </span>
  </div>
</template>
<script lang="ts">
import store from "@/store";
import { computed } from "vue";
export default {
  props: {
    appointment: { required: true, type: Object },
  },
  setup() {
    const userRole = computed(() => store.getters.userRole);
    const getTootTip = (allergies) => {
      var html = "";
      allergies.forEach((allergy) => {
        html = html + allergy.symptoms + " ";
      });
      return html;
    };

    return {
      userRole,
      getTootTip,
    };
  },
};
</script>
