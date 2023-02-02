<template>
  <div
    class="modal fade"
    id="modal_move_apt"
    tabindex="-1"
    aria-hidden="true"
    ref="MoveAptModalRef"
  >
    <div
      :class="
        'modal-dialog modal-dialog-centered ' +
        (aptData.step === 0 ? 'mw-500px' : 'mw-800px')
      "
    >
      <div class="modal-content">
        <div class="modal-header" id="kt_modal_move_appointment_header">
          <div class="d-block">
            <h2 class="fw-bolder">
              {{ aptData.action === "move" ? "Moving" : "Copy" }} Appointment:
            </h2>
            <div class="mt-1">
              <span class="me-1">{{ aptData?.patient_name?.full }},</span>
              <span class="me-1">{{ aptData?.aus_formatted_date }},</span>
              <span class="me-1">
                {{ aptData?.formatted_appointment_time }}
              </span>
            </div>
          </div>
          <h2 class="select-new-apt-caption" v-if="aptData.step === 1">
            Select New Appointment Time
          </h2>
          <div class="d-flex justify-content-between">
            <button
              class="btn btn-primary"
              v-if="aptData.step"
              @click.prevent="handleBack"
            >
              Back
            </button>
          </div>
          <div
            id="kt_modal_add_customer_close"
            data-bs-dismiss="modal"
            class="btn btn-icon btn-sm btn-active-icon-primary"
          >
            <span class="svg-icon svg-icon-1">
              <InlineSVG icon="cross" />
            </span>
          </div>
        </div>
        <div class="modal-body py-10 px-lg-8">
          <div
            class="scroll-y me-n7 pe-7"
            id="kt_modal_move_appointment_scroll"
            data-kt-scroll="true"
            data-kt-scroll-activate="{default: false, lg: true}"
            data-kt-scroll-max-height="auto"
            data-kt-scroll-dependencies="#kt_modal_move_appointment_header"
            data-kt-scroll-wrappers="#kt_modal_move_appointment_scroll"
            data-kt-scroll-offset="300px"
          >
            <el-form :model="formData" ref="formRef" v-if="aptData.step === 0">
              <div class="row appointment-type">
                <InputWrapper prop="appointment_type_id">
                  <el-select
                    :disabled="
                      props.isDisableAptTypeList && aptData.action === 'move'
                    "
                    class="w-100"
                    placeholder="Select Appointment Type"
                    v-model="formData.appointment_type_id"
                    @change="handleChangeAppointmentType"
                  >
                    <el-option
                      v-for="item in aptTypelist"
                      :value="item.id"
                      :label="item.name"
                      :key="item.id"
                    />
                  </el-select>
                </InputWrapper>
              </div>
              <div class="row">
                <InputWrapper class="col-6">
                  <el-select
                    class="w-100"
                    placeholder="Select Clinic"
                    v-model="formData.clinic_id"
                  >
                    <el-option value="" label="Any Clinic" />
                    <el-option
                      v-for="item in cliniclist"
                      :value="item.id"
                      :label="item.name"
                      :key="item.id"
                    />
                  </el-select>
                </InputWrapper>
                <InputWrapper class="col-6">
                  <el-select
                    class="w-100"
                    placeholder="Select Specialist"
                    v-model="formData.specialist_id"
                    filterable
                  >
                    <el-option value="" label="Any Specialist" />
                    <el-option
                      v-for="specialist in allSpecialist"
                      :value="specialist.id"
                      :label="specialist?.full_name"
                      :key="specialist.id"
                    />
                  </el-select>
                </InputWrapper>
              </div>
              <div class="row">
                <InputWrapper class="col-6">
                  <el-select
                    class="w-100"
                    placeholder="Select Appointment Time Requirement"
                    v-model="formData.time_requirement"
                  >
                    <el-option :value="0" label="Any time" :key="0" />
                    <el-option
                      v-for="item in aptTimeRequirelist"
                      :value="item.id"
                      :label="item.title"
                      :key="item.id"
                    />
                  </el-select>
                </InputWrapper>
                <div class="col-6 d-flex px-6">
                  <el-input
                    style="width: 100px"
                    type="number"
                    v-model="formData.timeframe_count"
                    min="0"
                    prop="timeframe_count"
                    placeholder=""
                  />
                  <el-select
                    class="w-100"
                    placeholder="Select Time frame"
                    v-model="formData.timeframe_type"
                  >
                    <el-option value="weeks" label="week(s)" />
                    <el-option value="months" label="month(s)" />
                    <el-option value="years" label="year(s)" />
                  </el-select>
                </div>
              </div>
              <div class="row">
                <div class="px-6">
                  <button
                    class="btn btn-primary mt-3 w-100"
                    @click.prevent="handleSearch"
                  >
                    SEARCH
                  </button>
                </div>
              </div>
            </el-form>
            <el-form
              :model="formData"
              ref="timeslotsformRef"
              v-if="aptData.step === 1"
            >
              <div class="apt-description">
                <span class="me-1">Clinic:</span>
                <span class="caption-content me-2">
                  {{
                    cliniclist.filter((c) => c.id === formData.clinic_id)[0]
                      ? cliniclist.filter((c) => c.id === formData.clinic_id)[0]
                          .name
                      : "Any"
                  }}
                </span>
                <span class="me-1">Specialist:</span>
                <span class="caption-content me-2">
                  {{
                    allSpecialist.filter(
                      (c) => c.id === formData.specialist_id
                    )[0]
                      ? allSpecialist.filter(
                          (c) => c.id === formData.specialist_id
                        )[0].full_name
                      : "Any"
                  }}
                </span>
                <span class="me-1">Time Requirement:</span>
                <span class="caption-content me-2">
                  {{
                    formData.time_requirement
                      ? aptTimeRequirelist.filter(
                          (c) => c.id === formData.time_requirement
                        )[0].title
                      : "Any"
                  }}
                </span>
                <span class="me-1">Time Frame:</span>
                <span class="caption-content me-2">
                  {{
                    formData.timeframe_count == 0
                      ? "This " + formData.timeframe_type.replace("s", "")
                      : formData.timeframe_count == 1
                      ? "Next " + formData.timeframe_type.replace("s", "")
                      : formData.timeframe_count + " " + formData.timeframe_type
                  }}
                </span>
                <span class="me-1">Appointment Type:</span>
                <span class="caption-content me-2">
                  {{ formData.appointment_type_name }}
                </span>
              </div>
              <WeeklyTimeSlotsTable
                :search="formData"
                :aptData="aptData"
                :allSpecialists="allSpecialist"
              ></WeeklyTimeSlotsTable>
            </el-form>
            <el-form
              :model="formData"
              ref="confirmformRef"
              v-if="aptData.step === 2"
              v-loading="loading"
            >
              <div class="">
                <h3>Please confirm:</h3>
              </div>
              <div class="d-flex mt-5 mb-5">
                <div class="apt-card d-flex flex-column gap-3">
                  <InfoSection :heading="'Patient'"
                    >{{ aptData.patient_name?.full }} ({{
                      aptData.patient_details?.contact_number
                    }})
                  </InfoSection>
                  <InfoSection :heading="'Clinic'">{{
                    aptData.clinic_details?.name
                  }}</InfoSection>

                  <InfoSection :heading="'Date'">
                    {{ aptData.aus_formatted_date }}
                  </InfoSection>

                  <InfoSection :heading="'Time'">
                    {{ aptData.formatted_appointment_time }}
                  </InfoSection>

                  <InfoSection :heading="'Type'">{{
                    aptData.appointment_type?.name
                  }}</InfoSection>

                  <InfoSection :heading="'Specialist'">{{
                    aptData.specialist_name
                  }}</InfoSection>
                </div>
                <div class="d-flex align-items-center me-2 ms-2">
                  <InlineSVG icon="next" />
                </div>
                <div class="apt-card d-flex flex-column gap-3">
                  <InfoSection :heading="'Patient'"
                    >{{ aptData.patient_name?.full }} ({{
                      aptData.patient_details?.contact_number
                    }})
                  </InfoSection>
                  <InfoSection :heading="'Clinic'">{{
                    bookingData?.clinic_name
                  }}</InfoSection>

                  <InfoSection :heading="'Date'">
                    {{ moment(bookingData.date).format("DD/MM/YYYY") }}
                  </InfoSection>

                  <InfoSection :heading="'Time'">
                    {{ moment(bookingData.time_slot[0]).format("HH:mm") }} -
                    {{ moment(bookingData.time_slot[1]).format("HH:mm") }}
                  </InfoSection>

                  <InfoSection :heading="'Type'">{{
                    aptData.appointment_type?.name
                  }}</InfoSection>

                  <InfoSection :heading="'Specialist'">{{
                    bookingData?.specialist_name
                  }}</InfoSection>
                </div>
              </div>
              <button
                class="btn btn-primary mt-3 w-100"
                @click.prevent="handleConfirm"
              >
                {{
                  aptData.action === "move"
                    ? "MOVE APPOINTMENT"
                    : "COPY APPOINTMENT"
                }}
              </button>
            </el-form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
#modal_move_apt {
  .el-divider--horizontal {
    margin: 0px;
  }
  .appointment-type label {
    display: none;
  }
  .select-new-apt-caption {
    color: red;
    text-transform: uppercase;
    font-weight: bold;
  }
  .apt-description {
    font-size: 16px;
    padding-left: 25px;
    padding-right: 25px;
  }
  .caption-content {
    color: #3e7ba0;
    font-weight: 700;
  }
  .apt-card {
    border-style: solid;
    border-radius: 10px;
    padding: 20px;
    border-color: #3e7ba0;
    border-width: 2px;
  }
}
</style>
<script>
import { defineComponent, computed, ref, onMounted, watch } from "vue";
import { Actions } from "@/store/enums/StoreEnums";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import WeeklyTimeSlotsTable from "@/components/appointments/partials/WeeklyTimeSlotsTable";
import {
  AppointmentMutations,
  AppointmentActions,
} from "@/store/enums/StoreAppointmentEnums";
import moment from "moment";
import { DrawerComponent } from "@/assets/ts/components/_DrawerComponent";
import chargeTypes from "@/core/data/charge-types";

export default defineComponent({
  name: "move-apt-modal",
  components: {
    WeeklyTimeSlotsTable,
  },
  props: {
    isDisableAptTypeList: { type: Boolean },
  },
  setup(props) {
    const store = useStore();
    const MoveAptModalRef = ref(null);
    const loading = ref(false);
    const aptData = computed(() => store.getters.getAptSelected);
    const aptTypelist = computed(() => store.getters.getAptTypesList);
    const cliniclist = computed(() => store.getters.clinicsList);
    const allSpecialist = computed(() => store.getters.getSpecialistList);
    const aptTimeRequirelist = computed(
      () => store.getters.getAptTimeRequireList
    );
    const bookingData = computed(() => store.getters.bookingDatas);
    const formData = ref({
      appointment_type_id: null,
      appointment_type_name: "",
      clinic_id: null,
      specialist_id: null,
      time_requirement: 0,
      timeframe_count: 0,
      timeframe_type: "weeks",
      date: null,
    });
    const aptInfoData = ref({
      clinic_name: "",
      clinic_id: "",
      send_forms: true,
      date: "",
      arrival_time: "",
      time_slot: ["2022-06-20T09:00", "2022-06-20T17:00"],
      appointment_type_id: "",
      specialist_id: "",
      room_id: "",
      note: "",
      patient_id: null,
      start_time: null,
    });
    const patientInfoData = ref({
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
    });

    const billingInfoData = ref({
      charge_type: chargeTypes[0].value,
      claim_sources: [],
      procedure_price: "",
      add_other_account_holder: false,
    });

    const otherInfoData = ref({
      anesthetic_questions: false,
      anesthetic_answers: [],
      referring_doctor_name: "",
      referring_doctor_id: "",
      referral_duration: "",
      referral_date: "",
      no_referral: false,
      no_referral_reason: "",
    });

    const handleChangeAppointmentType = () => {
      formData.value.appointment_type_name = aptTypelist.value.filter(
        (t) => t.id === formData.value.appointment_type_id
      )[0].name;
    };

    watch(aptData, () => {
      store.dispatch(AppointmentActions.APPOINTMENT_TYPES.LIST).then(() => {
        formData.value.appointment_type_id = aptData.value.appointment_type?.id;
        formData.value.appointment_type_name =
          aptData.value.appointment_type?.name;
      });

      formData.value.clinic_id = aptData.value.clinic_id;
      formData.value.specialist_id = aptData.value.specialist_id;
    });

    onMounted(() => {
      store.dispatch(Actions.APT_TIME_REQUIREMENT.LIST);
    });

    const handleSearch = () => {
      aptData.value.step = 1;
      // store.commit(AppointmentMutations.SET_APT.OTHER_SELECT, aptData.value);
    };

    const handleBack = () => {
      aptData.value.step = aptData.value.step - 1;
    };

    const handleConfirm = () => {
      loading.value = true;

      for (let key in aptInfoData.value) {
        if (key in aptData.value) aptInfoData.value[key] = aptData.value[key];
      }
      for (let key in patientInfoData.value)
        patientInfoData.value[key] = aptData.value.patient[key];
      for (let key in billingInfoData.value)
        if (aptData.value.patient.billing?.length)
          billingInfoData.value[key] = aptData.value.patient.billing[0][key];
      for (let key in otherInfoData.value)
        if (aptData.value.referral[key] !== undefined)
          otherInfoData.value[key] = aptData.value.referral[key];
      aptInfoData.value.date = moment(bookingData.value.date)
        .format("DD-MM-YYYY")
        .toString();
      aptInfoData.value.time_slot = bookingData.value.time_slot;
      aptInfoData.value.start_time = moment(
        bookingData.value.time_slot[0]
      ).format("HH:mm");
      aptInfoData.value.end_time = moment(
        bookingData.value.time_slot[1]
      ).format("HH:mm");
      aptInfoData.value.specialist_id = bookingData.value.specialist_id;
      aptInfoData.value.clinic_id = bookingData.value.clinic_id;
      aptInfoData.value.clinic_name = bookingData.value.clinic_name;

      aptInfoData.value.appointment_type_id =
        formData.value.appointment_type_id;

      let submitData = {
        ...aptInfoData.value,
        ...patientInfoData.value,
        ...billingInfoData.value,
        ...otherInfoData.value,
      };

      let submitAction = AppointmentActions.APT.CREATE;
      if (aptData.value.action === "move") {
        submitData.id = aptData.value.id;
        submitAction = AppointmentActions.APT.UPDATE;
      }

      store
        .dispatch(submitAction, submitData)
        .then(() => {
          store.dispatch(AppointmentActions.LIST).then(() => {
            loading.value = false;
            hideModal(MoveAptModalRef.value);
            if (aptData.value.action === "move") {
              DrawerComponent?.getInstance("appointment-drawer")?.toggle();
            } else {
              let newAptData = aptData.value;
              for (let key in submitData) newAptData[key] = submitData[key];
              store.commit(AppointmentMutations.SET_APT.SELECT, newAptData);
            }
          });
        })
        .catch(({ response }) => {
          loading.value = false;
        });
    };

    return {
      props,
      aptData,
      aptTypelist,
      cliniclist,
      allSpecialist,
      aptTimeRequirelist,
      formData,
      MoveAptModalRef,
      loading,
      handleSearch,
      handleConfirm,
      bookingData,
      moment,
      handleChangeAppointmentType,
      handleBack,
    };
  },
});
</script>
