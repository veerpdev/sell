<template>
  <!--begin::Modal - Create App-->
  <div
    class="modal fade"
    id="modal_check_in_apt"
    ref="createAptModalRef"
    tabindex="-1"
    aria-hidden="true"
    data-bs-backdrop="static"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-1000px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header">
          <!--begin::Modal title-->
          <div class="d-flex flex-column">
            <h2>Checking in {{ patient.full_name }}</h2>
            <h3>
              {{ appointment.appointment_type?.name }},
              {{ appointment.specialist_name }} @
              {{ appointment.formatted_appointment_time }}
            </h3>
          </div>
          <!--end::Modal title-->

          <!--begin::Close-->
          <button
            type="button"
            class="btn btn-lg btn-light-danger me-3 mb-1"
            data-bs-dismiss="modal"
          >
            <span class="svg-icon svg-icon-3 my-auto">
              <InlineSVG icon="cross" />
            </span>
            Cancel
          </button>
          <!--end::Close-->
        </div>
        <!--end::Modal header-->

        <!--begin::Modal body-->
        <div class="modal-body py-lg-10 px-lg-10">
          <!--begin::Stepper-->
          <div
            class="stepper stepper-pills stepper-column d-flex flex-column flex-xl-row flex-row-fluid"
            id="modal_check_in_apt_stepper"
            ref="checkInAptStepperRef"
          >
            <!--begin::Aside-->
            <div
              class="d-flex justify-content-center justify-content-xl-start flex-row w-100 w-xl-350px"
            >
              <!--begin::Nav-->
              <div class="">
                <StepperNavItem
                  @click="gotoPage(1)"
                  dataStepperElement="nav"
                  stepperNumber="1"
                  stepperTitle="Patient Details"
                  stepperDescription=""
                />

                <StepperNavItem
                  @click="gotoPage(2)"
                  dataStepperElement="nav"
                  stepperNumber="2"
                  stepperTitle="Billing Info"
                  stepperDescription=""
                />

                <StepperNavItem
                  @click="gotoPage(3)"
                  dataStepperElement="nav"
                  stepperNumber="3"
                  stepperTitle="Appointment Referral"
                  stepperDescription=""
                />

                <StepperNavItem
                  v-if="
                    appointment.appointment_type?.collecting_person_required
                  "
                  @click="gotoPage(4)"
                  dataStepperElement="nav"
                  stepperNumber="4"
                  stepperTitle="Collecting Person"
                  stepperDescription=""
                />

                <StepperNavItem
                  @click="
                    gotoPage(
                      appointment.appointment_type?.collecting_person_required
                        ? 5
                        : 4
                    )
                  "
                  dataStepperElement="nav"
                  :stepperNumber="
                    appointment.appointment_type?.collecting_person_required
                      ? '5'
                      : '4'
                  "
                  stepperTitle="Print Labels/Documents"
                  stepperDescription=""
                />
              </div>

              <!--end::Nav-->
            </div>
            <!--begin::Aside-->

            <div class="w-100 py-lg-5 px-lg-10">
              <div class="current" data-kt-stepper-element="content">
                <PatientDetailsForm
                  :patient="patient"
                  :on-submit-extras="onPatientDetailsSubmit"
                  button-text="Save and Next"
                />
              </div>

              <div data-kt-stepper-element="content" class="w-100">
                <PatientBillingForm
                  :patient="patient"
                  :appointment="appointment"
                  :on-submit-extras="onPatientBillingSubmit"
                  button-text="Save and Next"
                />
              </div>

              <div data-kt-stepper-element="content" class="w-100">
                <AppointmentReferralForm
                  :appointment="appointment"
                  :on-submit-extras="onPatientReferralSubmit"
                  button-text="Save and Next"
                />
              </div>

              <div
                v-if="appointment.appointment_type?.collecting_person_required"
                data-kt-stepper-element="content"
                class="w-100"
              >
                <AppointmentCollectingPersonForm
                  class="w-100"
                  :on-submit-extras="onCollectingPersonSubmit"
                  :appointment="appointment"
                  button-text="Save and Next"
                />
              </div>

              <div data-kt-stepper-element="content" class="w-100 min-h-100">
                <div
                  class="w-100 min-h-100 d-flex flex-column justify-content-between gap-4"
                >
                  <div class="d-flex flex-column gap-4">
                    <HeadingText text="Appointment Actions" />
                    <PrintLabelButton
                      :appointment="appointment"
                      :patient="patient"
                      class="w-100"
                    />

                    <PrintHospitalCertificateButton
                      :appointment="appointment"
                      :patient="patient"
                      class="w-100"
                    />
                  </div>

                  <div class="d-flex justify-content-end gap-4">
                    <!--begin::Button-->
                    <button
                      :data-kt-indicator="loading ? 'on' : null"
                      class="btn btn-lg btn-primary"
                      @click="handleCheckIn(true)"
                    >
                      <span v-if="!loading" class="indicator-label">
                        Make Payment and Check In
                      </span>
                      <span v-if="loading" class="indicator-progress">
                        Please wait...
                        <span
                          class="spinner-border spinner-border-sm align-middle ms-2"
                        ></span>
                      </span>
                    </button>

                    <button
                      :data-kt-indicator="loading ? 'on' : null"
                      class="btn btn-lg btn-primary"
                      @click="handleCheckIn(false)"
                    >
                      <span v-if="!loading" class="indicator-label">
                        Check In Only
                      </span>
                      <span v-if="loading" class="indicator-progress">
                        Please wait...
                        <span
                          class="spinner-border spinner-border-sm align-middle ms-2"
                        ></span>
                      </span>
                    </button>
                    <!--end::Button-->
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--end::Modal - Create App-->
</template>

<script lang="ts">
import { defineComponent, watch, ref, PropType } from "vue";
import { StepperComponent } from "@/assets/ts/components";
import StepperNavItem from "@/components/presets/StepperElements/StepperNavItem.vue";
import AppointmentCollectingPersonForm from "./partials/AppointmentCollectingPersonForm.vue";
import IAppointment from "@/store/interfaces/IAppointment";
import store from "@/store";
import PrintLabelButton from "../patients/patientAppointmentActions/PrintLabelButton.vue";
import IPatient from "@/store/interfaces/IPatient";
import PrintHospitalCertificateButton from "../patients/patientAppointmentActions/PrintHospitalCertificateButton.vue";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import { Actions } from "@/store/enums/StoreEnums";
import { useRouter } from "vue-router";
import { DrawerComponent } from "@/assets/ts/components/_DrawerComponent";
import PatientDetailsForm from "../patients/PatientDetailsForm.vue";
import PatientBillingForm from "../patients/billing/PatientBillingForm.vue";
import AppointmentReferralForm from "./partials/AppointmentReferralForm.vue";
import { Modal } from "bootstrap";

export default defineComponent({
  components: {
    StepperNavItem,
    AppointmentCollectingPersonForm,
    PrintLabelButton,
    PrintHospitalCertificateButton,
    PatientDetailsForm,
    PatientBillingForm,
    AppointmentReferralForm,
  },
  name: "check-in-modal",
  props: {
    appointment: { required: true, type: Object as PropType<IAppointment> },
    patient: { required: true, type: Object as PropType<IPatient> },
  },
  setup(props) {
    const checkInAptStepperRef = ref<HTMLElement>();
    const createAptModalRef = ref<HTMLElement>();
    const _stepperObj = ref<StepperComponent | null>(null);
    const router = useRouter();
    const currentPage = ref<number>(1);
    const loading = ref<boolean>(false);

    const gotoPage = (page) => {
      currentPage.value = page;
      if (_stepperObj.value) {
        _stepperObj.value.goto(page);
      }
    };

    watch(checkInAptStepperRef, () => {
      if (checkInAptStepperRef.value != undefined) {
        _stepperObj.value = StepperComponent.createInstance(
          checkInAptStepperRef.value
        );
      }
    });

    const onPatientDetailsSubmit = () => {
      gotoPage(2);
    };

    const onPatientBillingSubmit = () => {
      gotoPage(3);
    };

    const onPatientReferralSubmit = () => {
      gotoPage(4);
    };

    const onCollectingPersonSubmit = () => {
      gotoPage(5);
    };

    const handleCancel = () => {
      Modal.getInstance(createAptModalRef.value).hide();
    };

    const handleCheckIn = (is_pay) => {
      loading.value = true;
      store
        .dispatch(AppointmentActions.APT.CHECK_IN, props.appointment.id)
        .then(() => {
          if (is_pay) {
            router.push({
              name: "make-payment-pay",
              params: { id: props.appointment.id },
            });
          } else {
            store.dispatch(AppointmentActions.LIST);
          }
        })
        .finally(() => {
          loading.value = false;
          DrawerComponent?.getInstance("appointment-drawer")?.hide();
          handleCancel();
        });
    };

    return {
      handleCheckIn,
      onPatientDetailsSubmit,
      onPatientBillingSubmit,
      onPatientReferralSubmit,
      onCollectingPersonSubmit,
      gotoPage,
      checkInAptStepperRef,
      createAptModalRef,
      loading,
      handleCancel,
    };
  },
});
</script>
