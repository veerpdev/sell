<template>
  <div
    class="modal fade"
    id="modal_appointment_referral"
    tabindex="-1"
    aria-hidden="true"
    ref="doctorAddressBookModalRef"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_collecting_person_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">Update Referral</h2>
          <!--end::Modal title-->

          <!--begin::Close-->
          <div
            id="kt_modal_collecting_person_close"
            data-bs-dismiss="modal"
            class="btn btn-icon btn-sm btn-active-icon-primary"
          >
            <span class="svg-icon svg-icon-1">
              <InlineSVG icon="cross" />
            </span>
          </div>

          <!--end::Close-->
        </div>
        <!--end::Modal header-->

        <div class="modal-body py-lg-10 px-lg-10">
          <AppointmentReferralForm
            :appointment="appointment"
            :on-submit-extras="closeModal"
            v-on:cancel="closeModal"
            showCancel
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType, ref } from "vue";
import AppointmentReferralForm from "@/components/appointments/partials/AppointmentReferralForm.vue";
import { Modal } from "bootstrap";
import IAppointment from "@/store/interfaces/IAppointment";

export default defineComponent({
  name: "update-appointment-referral-modal",
  components: { AppointmentReferralForm },
  props: {
    appointment: { type: Object as PropType<IAppointment>, required: true },
  },
  setup() {
    const doctorAddressBookModalRef = ref<HTMLElement>();

    const closeModal = () => {
      Modal.getInstance(doctorAddressBookModalRef.value).hide();
    };

    return {
      doctorAddressBookModalRef,
      closeModal,
    };
  },
});
</script>
