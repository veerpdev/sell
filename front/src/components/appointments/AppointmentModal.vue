<template>
  <!--begin::Modal - Create App-->
  <div
    class="modal fade"
    :id="'modal_' + modalId + '_apt'"
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
          <h2>{{ title }}</h2>
          <!--end::Modal title-->

          <!--begin::Close-->
          <button
            type="button"
            class="btn btn-lg btn-light-danger me-3 mb-1"
            @click="handleCancel"
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
        <el-container v-loading="loading">
          <div class="modal-body py-lg-10 px-lg-10">
            <!--begin::Stepper-->
            <div
              class="stepper stepper-pills stepper-column d-flex flex-column flex-xl-row flex-row-fluid"
              id="modal_create_apt_stepper"
              ref="createAptRef"
            >
              <!--begin::Aside-->
              <div
                class="d-flex justify-content-center justify-content-xl-start flex-row-auto w-100 w-xl-350px"
              >
                <!--begin::Nav-->
                <div class="stepper-nav ps-lg-10">
                  <stepper-nav @go-to="gotoPage" />

                  <!--begin::Appointment Overview-->
                  <apt-overview
                    :apt-info-data="aptInfoData"
                    :patient-info-data="patientInfoData"
                    :specialist="cur_specialist"
                  />
                  <!--end::Appointment Overview-->
                  <apt-wait-listed
                    :wait-listed="aptInfoData.is_wait_listed"
                    @update="aptWaitListedUpdate"
                  />
                </div>
                <!--end::Nav-->
              </div>
              <!--begin::Aside-->

              <!--begin::Content-->
              <div class="flex-row-fluid py-lg-5 px-lg-15">
                <!--begin::Step 1-->
                <step-one
                  :loading="loading"
                  :modal-id="modalId"
                  :patient-info-data="patientInfoData"
                  :aptInfoDataE="aptInfoData"
                  ref="stepOneRef"
                  @save="processSave"
                  @process="processStepOne"
                  @changeAptType="changeAptType"
                  @update="processUpdate"
                />
                <!--end::Step 1-->

                <!--begin::Step 2-->
                <step-two
                  :loading="loading"
                  :modal-id="modalId"
                  :isNewPatient="isNewPatient"
                  :patientStatus="patientStatus"
                  :patientDataE="patientInfoData"
                  ref="stepTwoRef"
                  @save="processSave"
                  @process="processStepTwo"
                  @updatePatient="setPatient"
                  @go-back="previousStep"
                />
                <!--end::Step 2-->

                <!--begin::Step 3 -->
                <step-three
                  :loading="loading"
                  :modal-id="modalId"
                  :patient-data-e="patientInfoData"
                  :billing-data-e="billingInfoData"
                  :apt-info-data-e="aptInfoData"
                  ref="stepThreeRef"
                  @save="processSave"
                  @process="processStepThree"
                  @go-back="previousStep"
                />
                <!--end::Step 3-->

                <!--begin::Step 4 -->
                <step-four
                  :apt-info-data-e="aptInfoData"
                  :loading="loading"
                  :modal-id="modalId"
                  :other-data-e="otherInfoData"
                  :patient-status="patientStatus"
                  ref="stepFourRef"
                  @go-back="previousStep"
                  @save="processSave"
                  @process="processStepFour"
                />
                <!--end::Step 4-->
              </div>
              <!--end::Content-->
            </div>
            <!--end::Stepper-->
          </div>
          <!--end::Modal body-->
        </el-container>
      </div>
      <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
  </div>
  <!--end::Modal - Create App-->
</template>
<script lang="ts">
import {
  defineComponent,
  onMounted,
  ref,
  watchEffect,
  computed,
  watch,
} from "vue";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import {
  AppointmentActions,
  AppointmentMutations,
} from "@/store/enums/StoreAppointmentEnums";
import { StepperComponent } from "@/assets/ts/components";
import { hideModal } from "@/core/helpers/dom";
import moment from "moment";
import chargeTypes, { getProcedurePrice } from "@/core/data/charge-types";
import { useRouter } from "vue-router";

import StepOne from "@/components/appointments/partials/ModalAptStepOne.vue";
import StepTwo from "@/components/appointments/partials/ModalAptStepTwo.vue";
import StepThree from "@/components/appointments/partials/ModalAptStepThree.vue";
import StepFour from "@/components/appointments/partials/ModalAptStepFour.vue";
import AptOverview from "@/components/appointments/partials/AppointmentOverview.vue";
import StepperNav from "@/components/appointments/partials/ModalAptStepperNav.vue";
import AptWaitListed from "@/components/appointments/partials/ModalAptWaitListed.vue";

import { ElMessage } from "element-plus";
import Swal from "sweetalert2";
import {
  IAptInfoData,
  IBillingInfoData,
  ICurSpecialist,
  IOtherInfoData,
  IPatientInfoData,
} from "@/assets/ts/components/_CreateAppointmentComponent";

export default defineComponent({
  props: {
    modalId: { type: String, required: true },
  },

  name: "Apt-Modal",
  components: {
    StepperNav,
    AptOverview,
    StepOne,
    StepTwo,
    StepThree,
    StepFour,
    AptWaitListed,
  },

  setup(props) {
    const store = useStore();
    const router = useRouter();
    const loading = ref(false);

    const aptInfoData = ref<IAptInfoData>({
      clinic_name: "",
      clinic_id: -1,
      send_forms: true,
      date: "",
      arrival_time: "",
      time_slot: ["2022-06-20T09:00", "2022-06-20T17:00"],
      appointment_type_id: null,
      specialist_id: -1,
      room_id: "",
      note: "",
      patient_id: null,
      start_time: "",
      is_wait_listed: 0,
    });

    const patientInfoData = ref<IPatientInfoData>({
      first_name: "",
      last_name: "",
      date_of_birth: "",
      email: "",
      address: "",
      contact_number: "",
      appointment_confirm_method: "",
      allergies: "",
      clinical_alerts: "",
      also_known_as: [],
      is_exist: false,
      is_ok: false,
      alerts: [],
    });

    const billingInfoData = ref<IBillingInfoData>({
      charge_type: chargeTypes[0].value,
      claim_sources: [],
      procedure_price: 0,
      add_other_account_holder: false,
    });

    const otherInfoData = ref<IOtherInfoData>({
      anesthetic_questions: false,
      anesthetic_answers: [],
      doctor_address_book_name: "",
      doctor_address_book_id: null,
      referral_duration: "",
      referral_date: "",
      no_referral: false,
      no_referral_reason: "",
    });

    const cur_specialist = ref<ICurSpecialist>({
      id: 0,
      full_name: "",
    });

    const appointment_time = ref<number>(30);
    const _appointment_time = ref<number>(30);
    const currentStepIndex = ref<number>(0);
    const arrival_time = ref<number>(30);
    const cur_appointment_type_id = ref<number | null>(null);

    const _stepperObj = ref<any>();

    const title = ref<string>("");
    const start_time = ref<string>("");
    const end_time = ref<string>("");
    const patientStatus = ref<string>("new");

    const isNewPatient = ref<boolean>(false);

    const stepOneRef = ref();
    const stepTwoRef = ref();
    const stepThreeRef = ref();
    const stepFourRef = ref();
    const createAptRef = ref();
    const createAptModalRef = ref(null);

    const aptTypeList = computed(() => store.getters.getAptTypesList);
    const aptData = computed(() => store.getters.getAptSelected);
    const aptList = computed(() => store.getters.getAptList);
    const bookingData = computed(() => store.getters.bookingDatas);
    const orgData = computed(() => store.getters.userOrganization);

    // Setting modal Heading and Ids
    const setTitle = () => {
      if (props.modalId === "new") {
        title.value = "Create Appointment";
      } else if (props.modalId === "update") {
        title.value = "Update Appointment";
      }
    };

    const formatDate = (date) => {
      return moment(date).format("DD-MM-YYYY").toString();
    };

    const updateAptTime = (startTime, endTime) => {
      aptInfoData.value.time_slot = [];
      aptInfoData.value.time_slot.push(
        moment(startTime, "HH:mm").format("HH:mm")
      );
      start_time.value = aptInfoData.value.time_slot[0];
      aptInfoData.value.time_slot.push(
        moment(endTime, "HH:mm").format("HH:mm")
      );
      aptInfoData.value.start_time = moment(startTime, "HH:mm").format("HH:mm");
    };

    // Get Selected APT data when user edit appointments
    watch(aptData, () => {
      if (props.modalId == "update") {
        for (let key in aptInfoData.value)
          aptInfoData.value[key] = aptData.value[key];
        cur_appointment_type_id.value = aptData.value.appointment_type_id;
        cur_specialist.value = aptData.value.specialist;
        for (let key in patientInfoData.value)
          patientInfoData.value[key] = aptData.value.patient[key];
        //for (let key in billingInfoData.value)
        // billingInfoData.value[key] = aptData.value.patient.billing[0][key];
        for (let key in otherInfoData.value)
          otherInfoData.value[key] = aptData.value.referral[key];

        aptInfoData.value.clinic_id = aptData.value.clinic_id;
        aptInfoData.value.clinic_name = aptData.value.clinic.name;
        billingInfoData.value.charge_type = aptData.value.charge_type;
        aptInfoData.value.date = moment(aptData.value.date)
          .format("DD-MM-YYYY")
          .toString();
        store.dispatch(Actions.CLINICS.ROOMS.LIST, aptInfoData.value.clinic_id);
        updateAptTime(aptData.value.start_time, aptData.value.end_time);
      }
    });

    watch(cur_appointment_type_id, () => {
      let apt_type = "";
      aptInfoData.value.appointment_type_id = cur_appointment_type_id.value;
      const _selected = aptTypeList.value.filter(
        (aptType) => aptType.id === cur_appointment_type_id.value
      )[0];

      // setting up selected appointment type
      if (typeof _selected === "undefined") {
        _appointment_time.value = Number(appointment_time.value);
        arrival_time.value = 30;
        apt_type = "";
      } else {
        appointment_time.value = orgData.value.appointment_length;
        _appointment_time.value = Number(
          appointment_time.value * _selected.appointment_length_as_number
        );
        arrival_time.value = Number(_selected.arrival_time);
        apt_type = _selected.type;
      }

      if (props.modalId == "update") {
        updateAptTime(aptData.value.start_time, aptData.value.end_time);
      }

      end_time.value = moment(start_time.value, "HH:mm")
        .add(_appointment_time.value, "minutes")
        .format("HH:mm")
        .toString();
      updateAptTime(start_time.value, end_time.value);
      aptInfoData.value.arrival_time = moment(start_time.value, "HH:mm")
        .subtract(arrival_time.value, "minutes")
        .format("HH:mm")
        .toString();

      if (props.modalId == "new") {
        if (apt_type === "CONSULTATION") {
          otherInfoData.value.anesthetic_questions = false;
        }
      }
    });

    watch(cur_specialist, () => {
      aptInfoData.value.specialist_id = Number(cur_specialist.value?.id);
    });

    watch(start_time, () => {
      aptInfoData.value.arrival_time = moment(start_time.value, "HH:mm")
        .subtract(arrival_time.value, "minutes")
        .format("HH:mm")
        .toString();
      end_time.value = moment(start_time.value, "HH:mm")
        .add(_appointment_time.value, "minutes")
        .format("HH:mm")
        .toString();
      if (props.modalId == "new") {
        updateAptTime(start_time.value, end_time.value);
      }
    });

    watchEffect(() => {
      const bookingData = store.getters.bookingDatas;

      if (bookingData.time_slot) {
        start_time.value = moment(bookingData.time_slot[0]).format("HH:mm");
        end_time.value = moment(bookingData.time_slot[1]).format("HH:mm");
      }

      if (bookingData.selected_specialist && props.modalId === "new") {
        cur_specialist.value = bookingData.selected_specialist;

        if (bookingData.selected_specialist) {
          const clinic =
            bookingData.selected_specialist.hrm_work_schedule[0].clinic;
          aptInfoData.value.clinic_name = String(clinic.name);
          aptInfoData.value.clinic_id = Number(clinic.id);
          if (props.modalId === "new") {
            aptInfoData.value.date = bookingData.date;
          }
          if (bookingData.appointment_type) {
            cur_appointment_type_id.value = bookingData.appointment_type.id;
          }
          store.dispatch(
            Actions.CLINICS.ROOMS.LIST,
            aptInfoData.value.clinic_id
          );
        }
      }

      if (cur_appointment_type_id.value && billingInfoData.value.charge_type) {
        const filteredApt = aptTypeList.value.filter(
          (item) => item.id === cur_appointment_type_id.value
        )[0];
        billingInfoData.value.procedure_price = getProcedurePrice(
          filteredApt,
          billingInfoData.value.charge_type
        );
      }
    });

    onMounted(() => {
      setTitle();
      _stepperObj.value = StepperComponent.createInstance(createAptRef.value);
      if (props.modalId === "new") {
        store.dispatch(Actions.DOCTOR_ADDRESS_BOOK.LIST);
      }
      store.dispatch(Actions.HEALTH_FUND.LIST);
      store.dispatch(Actions.ANESTHETIST_QUES.ACTIVE_LIST);
      store.dispatch(AppointmentActions.APPOINTMENT_TYPES.LIST);
      const myModalEl = document.getElementById("modal_new_apt");
      myModalEl?.addEventListener("hide.bs.modal", () => {
        const draftAptId = store.getters.getDraftAptId;
        if (draftAptId && aptInfoData.value.date) {
          store
            .dispatch(AppointmentActions.APT.DRAFT.DELETE, draftAptId)
            .then(() => {
              store.dispatch(AppointmentActions.LIST, {
                date: aptInfoData.value.date,
              });
            });
          store.commit(AppointmentMutations.DRAFT.SET, null);
        }
      });
    });

    // Send request to update exiting appointment
    const handleSave = async () => {
      await checkAptOverlap();
      loading.value = true;
      aptInfoData.value.appointment_type_id = cur_appointment_type_id.value;
      store
        .dispatch(AppointmentActions.APT.UPDATE, {
          id: aptData.value.id,
          ...aptInfoData.value,
          ...patientInfoData.value,
          ...billingInfoData.value,
          ...otherInfoData.value,
        })
        .then(() => {
          store.dispatch(AppointmentActions.LIST);
          hideModal(createAptModalRef.value);
          resetCreateModal();
          ElMessage.success("Appointment Saved");
        })
        .finally(() => {
          loading.value = false;
        });
    };

    const resetCreateModal = () => {
      currentStepIndex.value = 0;
      _stepperObj.value.goFirst();

      if (props.modalId == "new") {
        stepOneRef.value.resetAll();
        stepThreeRef.value.formRef.resetFields();
        stepFourRef.value.formRef.resetFields();
        if (stepTwoRef.value.formRef) stepTwoRef.value.formRef.resetFields();

        for (let key in stepTwoRef.value.filterPatient)
          stepTwoRef.value.filterPatient[key] = "";
        for (let key in stepTwoRef.value.filterPatient)
          stepTwoRef.value.filterPatient[key] = "";
        for (let key in patientInfoData.value) patientInfoData.value[key] = "";
        for (let key in billingInfoData.value) billingInfoData.value[key] = "";

        cur_appointment_type_id.value = null;
        billingInfoData.value.claim_sources = [];
        patientInfoData.value.also_known_as = [];
        otherInfoData.value.doctor_address_book_name = "";
        stepFourRef.value.otherInfoData.doctor_address_book_name = "";
        stepThreeRef.value.billingInfoData.claim_sources = [];
        stepThreeRef.value.patientInfoData.also_known_as = [];
        patientStatus.value = "new";
        aptInfoData.value.is_wait_listed = 0;
      } else {
        store.dispatch(PatientActions.LIST);
      }
    };

    const handleCancel = () => {
      _stepperObj.value = StepperComponent.createInstance(createAptRef.value);
      resetCreateModal();
    };

    const previousStep = () => {
      if (!_stepperObj.value) {
        return;
      }
      currentStepIndex.value--;
      _stepperObj.value.goPrev();
    };

    const createApt = () => {
      let newAptId = null;
      const billingInfo = billingInfoData.value;
      const patientInfo = patientInfoData.value;

      billingInfo.claim_sources = billingInfo.claim_sources?.filter(
        (source) => {
          return !Object.prototype.hasOwnProperty.call(source, "id");
        }
      );
      patientInfo.also_known_as = patientInfo?.also_known_as?.filter(
        (source) => {
          return !Object.prototype.hasOwnProperty.call(source, "id");
        }
      );

      // FORMAT patient date of birth
      patientInfo.date_of_birth = formatDate(patientInfo.date_of_birth);
      loading.value = true;
      store
        .dispatch(AppointmentActions.APT.CREATE, {
          ...aptInfoData.value,
          ...patientInfo,
          ...billingInfo,
          ...otherInfoData.value,
        })
        .then((data) => {
          newAptId = data.data.data.id;
          store
            .dispatch(AppointmentActions.LIST, {
              date: bookingData.value.date,
            })
            .finally(() => {
              loading.value = false;
              handleCancel();
              Swal.fire({
                text: "Successfully Created!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                  cancelButton: "btn btn-info",
                },
                showCancelButton: true,
                cancelButtonText: "Deposit",
              }).then((result) => {
                hideModal(createAptModalRef.value);
                resetCreateModal();
                if (result.isDismissed && newAptId) {
                  router.push({
                    name: "make-payment-pay",
                    params: { id: newAptId },
                  });
                }
              });
            });
        });
    };

    const updateApt = () => {
      loading.value = true;
      const billingInfo = billingInfoData.value;
      const patientInfo = patientInfoData.value;
      aptInfoData.value.appointment_type_id = cur_appointment_type_id.value;

      billingInfo.claim_sources = billingInfo.claim_sources.filter((source) => {
        return !Object.prototype.hasOwnProperty.call(source, "id");
      });

      patientInfo.also_known_as = patientInfo.also_known_as.filter((source) => {
        return !Object.prototype.hasOwnProperty.call(source, "id");
      });

      store
        .dispatch(AppointmentActions.APT.UPDATE, {
          id: aptData.value.id,
          ...aptInfoData.value,
          ...patientInfo,
          ...billingInfo,
          ...otherInfoData.value,
        })
        .then(() => {
          loading.value = false;
          store.dispatch(AppointmentActions.LIST);
        })
        .finally(() => {
          loading.value = false;
        });
    };

    const gotoPage = async (page) => {
      await checkAptOverlap();
      if (props.modalId === "update") {
        currentStepIndex.value = Number(page - 1);
        _stepperObj.value.goto(page);
      }
    };

    const checkAptOverlap = () => {
      const startTime = aptInfoData.value.time_slot[0] + ":00";
      const endTime = aptInfoData.value.time_slot[1] + ":00";
      let filteredAptList = aptList.value.filter((apt) => {
        if (
          !apt.draft_status &&
          aptInfoData.value.specialist_id === apt.specialist_id &&
          aptInfoData.value.clinic_id === apt.clinic_id
        ) {
          if (startTime < apt.end_time && apt.start_time < endTime) {
            return apt;
          }
        }
      });

      if (props.modalId === "update") {
        filteredAptList = filteredAptList.filter(
          (apt) => apt.id !== aptData.value.id
        );
      }

      if (filteredAptList.length > 0) {
        return new Promise(function (resolve) {
          Swal.fire({
            title: "Are you sure?",
            text:
              "You already have an appointment at " +
              filteredAptList[0].start_time +
              " - " +
              filteredAptList[0].end_time +
              ", This action will overlap with existing appointment!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, do it!",
          }).then((result) => {
            if (result.value) {
              resolve(null);
            }
          });
        });
      }
    };

    // Handle appointment data that are coming from step one component
    const processStepOne = async (aptNewData) => {
      await checkAptOverlap();
      for (let key in aptNewData) aptInfoData.value[key] = aptNewData[key];
      isNewPatient.value = aptNewData.isNewPatient;

      patientStatus.value = aptNewData.isNewPatient ? "new" : "existing";

      moveToNextStep();
      store.dispatch(PatientActions.LIST);
    };

    //Update parent objects and send PUT request
    const processSave = (data, step) => {
      processUpdate(data, step);
      handleSave();
    };

    // Just update parent objects without saving them
    const processUpdate = (data, step) => {
      if (step === 1) {
        for (let key in data) aptInfoData.value[key] = data[key];
      } else if (step === 2) {
        for (let key in data) patientInfoData.value[key] = data[key];
      } else if (step === 3) {
        for (let key in data) billingInfoData.value[key] = data[key];
      } else if (step === 4) {
        for (let key in data) otherInfoData.value[key] = data[key];
      }
    };

    const changeAptType = (data) => {
      aptInfoData.value.appointment_type_id = data;
      cur_appointment_type_id.value = data;
    };

    // Handle Patient data that are coming from step two component
    const processStepTwo = async (patientData, billingData) => {
      for (let key in patientData)
        patientInfoData.value[key] = patientData[key];
      for (let key in billingData)
        billingInfoData.value[key] = billingData[key];

      moveToNextStep();
    };

    const processStepThree = (billingData) => {
      for (let key in billingData)
        billingInfoData.value[key] = billingData[key];
      moveToNextStep();
    };

    const processStepFour = (otherNewData) => {
      loading.value = true;
      for (let key in otherNewData)
        otherInfoData.value[key] = otherNewData[key];
      props.modalId === "new" ? createApt() : updateApt();
      resetCreateModal();
    };

    const aptWaitListedUpdate = (isWaitListed) => {
      aptInfoData.value.is_wait_listed = isWaitListed;
    };

    const moveToNextStep = () => {
      currentStepIndex.value++;
      if (!_stepperObj.value) return;

      _stepperObj.value.goNext();
    };

    const setPatient = (patientId: number) =>
      (aptInfoData.value.patient_id = patientId);
    return {
      cur_specialist,
      loading,
      createAptRef,
      createAptModalRef,
      patientStatus,
      aptInfoData,
      patientInfoData,
      billingInfoData,
      otherInfoData,
      title,
      isNewPatient,
      previousStep,
      handleCancel,
      gotoPage,
      processStepOne,
      processSave,
      changeAptType,
      processStepTwo,
      processStepThree,
      processStepFour,
      processUpdate,
      aptWaitListedUpdate,
      setPatient,
      stepOneRef,
      stepTwoRef,
      stepThreeRef,
      stepFourRef,
    };
  },
});
</script>
