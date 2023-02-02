<template>
  <LargeIconButton text="View Referral" @click="handleReferral()" />
</template>

<script lang="ts">
import { Modal } from "bootstrap";
import AppointmentReferralModal from "@/views/patients/modals/AppointmentReferralModal.vue";
import { PropType } from "vue";
import IAppointment from "@/store/interfaces/IAppointment";

export default {
  props: {
    appointment: {
      required: true,
      type: Object as PropType<IAppointment>,
    },
  },
  setup() {
    const handleReferral = () => {
      setTimeout(() => {
        const modal = new Modal(
          document.getElementById("modal_appointment_referral")
        );
        modal.show();
      }, 100);
    };

    return { handleReferral };
  },
};
</script>
