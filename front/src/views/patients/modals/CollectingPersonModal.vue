<template>
  <ModalWrapper
    title="Update Collecting Person"
    modalId="collecting_person"
    :modalRef="collectingPersonModalRef"
  >
    <AppointmentCollectingPersonForm
      :onSubmitExtras="onSubmitExtras"
      :appointment="appointment"
    />
  </ModalWrapper>
</template>

<script lang="ts">
import { defineComponent, ref, PropType } from "vue";
import IAppointment from "@/store/interfaces/IAppointment";
import { mask } from "vue-the-mask";
import { hideModal } from "@/core/helpers/dom";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import store from "@/store";
import AppointmentCollectingPersonForm from "@/components/appointments/partials/AppointmentCollectingPersonForm.vue";
import { displaySuccessToast } from "@/helpers/helpers";
export default defineComponent({
  name: "update-collecting-person-modal",
  directives: {
    mask,
  },
  components: { AppointmentCollectingPersonForm },
  props: {
    appointment: { type: Object as PropType<IAppointment>, required: true },
  },
  setup(props) {
    const collectingPersonModalRef = ref(null);

    const onSubmitExtras = () => {
      hideModal(collectingPersonModalRef.value);
      store.dispatch(PatientActions.VIEW, props.appointment.patient_id);
      displaySuccessToast("Collecting person updated");
    };

    return {
      onSubmitExtras,
      collectingPersonModalRef,
    };
  },
});
</script>
