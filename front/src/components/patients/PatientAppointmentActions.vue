<template>
  <!--Specialist only actions-->
  <template v-if="userRole == 'specialist'">
    <AddRecallButton :appointment="appointment" :patient="patient" />
    <AddAudioButton :appointment="appointment" :patient="patient" />
    <CreateDocumentButton
      :appointment="appointment"
      :patient="patient"
      document-type="letter"
    />
    <CreateDocumentButton
      :appointment="appointment"
      :patient="patient"
      document-type="report"
    />
    <CreateDocumentButton
      :appointment="appointment"
      :patient="patient"
      document-type="referral"
    />
  </template>

  <!--Admin only actions-->
  <template v-if="userRole != 'specialist'">
    <MakePaymentButton :appointment="appointment" :patient="patient" />
    <PrintLabelButton :appointment="appointment" :patient="patient" />
    <PrintHospitalCertificateButton
      :appointment="appointment"
      :patient="patient"
    />
  </template>

  <!--Actions for every body-->
  <PreAdmissionFormButton
    v-if="appointment.procedure_approval_status !== 'NOT_RELEVANT'"
    :appointment="appointment"
  />
</template>

<script lang="ts">
import { defineComponent, computed, PropType } from "vue";
import store from "@/store";
import PreAdmissionFormButton from "./patientAppointmentActions/PreAdmissionFormButton.vue";
import CreateDocumentButton from "./patientAppointmentActions/CreateDocumentButton.vue";
import PrintLabelButton from "./patientAppointmentActions/PrintLabelButton.vue";
import AddRecallButton from "./patientAppointmentActions/AddRecallButton.vue";
import AddAudioButton from "./patientAppointmentActions/AddAudioButton.vue";
import PrintHospitalCertificateButton from "./patientAppointmentActions/PrintHospitalCertificateButton.vue";
import MakePaymentButton from "./patientAppointmentActions/MakePaymentButton.vue";
import IAppointment from "@/store/interfaces/IAppointment";
import IPatient from "@/store/interfaces/IPatient";
export default defineComponent({
  name: "patient-appointment-actions",
  components: {
    PreAdmissionFormButton,
    CreateDocumentButton,
    PrintLabelButton,
    PrintHospitalCertificateButton,
    AddRecallButton,
    AddAudioButton,
    MakePaymentButton,
  },
  props: {
    appointment: {
      required: true,
      type: Object as PropType<IAppointment>,
    },
    patient: {
      required: true,
      type: Object as PropType<IPatient>,
    },
  },

  setup() {
    const userRole = computed(() => store.getters.userRole);

    return {
      userRole,
    };
  },
});
</script>
