<template>
  <div data-kt-stepper-element="content">
    <div class="w-100">
      <el-form
        class="w-100"
        :model="otherInfoData"
        :rules="rules"
        ref="formRef"
        @submit.prevent=""
      >
        <div class="row scroll h-500px">
          <div v-if="isAnesthetistRequired" class="card-info">
            <div class="fs-3 fw-bold text-muted mb-6">
              Pre-Procedure Questions
            </div>
            <div class="row">
              <div class="col-sm-6">
                <!--begin::Input group-->
                <div class="fv-row mb-7">
                  <!--begin::Input-->
                  <el-form-item prop="anesthetic_questions">
                    <el-checkbox
                      class="w-100"
                      v-model="otherInfoData.anesthetic_questions"
                      label="Anesthetic Questions"
                      data-bs-toggle="collapse"
                      href="#toogle_ane_ques"
                    />
                  </el-form-item>
                  <!--end::Input-->
                </div>
                <!--end::Input group-->
              </div>
              <div class="col-sm-12 collapse" id="toogle_ane_ques">
                <template v-for="(item, idx) in aneQuestions" :key="idx">
                  <div class="row mb-2">
                    <div class="col-10">{{ item.question }}</div>
                    <div class="col-2">
                      <el-switch
                        v-model="aneAnswers[idx]"
                        :active-value="true"
                        :inactive-value="false"
                        @change="handleAneQuestions"
                        style="
                          --el-switch-on-color: #13ce66;
                          --el-switch-off-color: #ff4949;
                        "
                        active-text="Y"
                        inactive-text="N"
                      />
                    </div>
                  </div>
                </template>
              </div>
              <el-divider />
            </div>
          </div>
          <!--start::Referral Information-->
          <div class="card-info">
            <div class="mb-6 d-flex justify-content-between">
              <span class="fs-3 fw-bold text-muted">Referral</span>
              <el-checkbox
                type="checkbox"
                v-model="otherInfoData.no_referral"
                label="Referring Doctor"
              />
            </div>
            <div class="row">
              <template v-if="otherInfoData.no_referral">
                <InputWrapper
                  class="col-6"
                  label="No Doctor Reason"
                  prop="no_doctor_reason"
                >
                  <el-input
                    type="text"
                    v-model="otherInfoData.no_referral_reason"
                    placeholder="Please Enter Reason"
                  />
                </InputWrapper>
              </template>
              <template v-else>
                <InputWrapper
                  class="col-6"
                  label="Doctor Address Book"
                  prop="doctor_address_book_id"
                >
                  <el-autocomplete
                    class="w-100"
                    v-model="otherInfoData.doctor_address_book_name"
                    value-key="full_name"
                    :fetch-suggestions="searchDoctorAddressBook"
                    placeholder="Please input"
                    :trigger-on-focus="false"
                    @select="handleSelectDoctorAddressBook"
                  >
                    <template #default="{ item }">
                      <div class="name">
                        {{ item.title }}
                        {{ item.first_name }}
                        {{ item.last_name }}
                      </div>
                      <div class="address">
                        {{ item.address }}
                      </div>
                    </template>
                  </el-autocomplete>
                </InputWrapper>
                <InputWrapper
                  class="col-6"
                  label="Referral Duration"
                  prop="referral_duration"
                >
                  <el-select
                    class="w-100"
                    v-model="otherInfoData.referral_duration"
                    placeholder="Enter Referral Duration"
                  >
                    <el-option value="0" label="Indefinite" />
                    <el-option value="3" label="3 Months" />
                    <el-option value="12" label="12 Months" />
                  </el-select>
                </InputWrapper>

                <InputWrapper
                  class="col-6"
                  label="Referral Date"
                  prop="referral_date"
                >
                  <el-date-picker
                    editable
                    class="w-100"
                    format="DD-MM-YYYY"
                    v-model="otherInfoData.referral_date"
                  />
                </InputWrapper>
              </template>
            </div>
          </div>
          <!--end::Referral Information-->
          <!--start::Appointment History-->
          <div class="card-info" v-if="patientStatus === 'exist'">
            <span class="fs-3 fw-bold text-muted">Appointment History</span>

            <div style="color: grey">
              <span class="me-2"
                >Total Appointments:
                {{ patientAptData.appointment_count }}
              </span>
              <span class="me-2">
                <span class="me-2">/</span>Cancelled:
                {{ patientAptData.cancelled_appointment_count }}
              </span>
              <span class="me-2">
                <span class="me-2">/</span>Missed:
                {{ patientAptData.missed_appointment_count }}
              </span>
            </div>

            <AppointmentHistory
              :pastAppointments="patientAptData.pastAppointments"
              :futureAppointments="patientAptData.futureAppointments"
            />
          </div>
        </div>
        <!--end::Appointment History-->

        <div class="d-flex justify-content-between">
          <button
            type="button"
            class="btn btn-lg btn-light-primary me-3"
            data-kt-stepper-action="previous"
            @click="previousStep"
          >
            <span class="svg-icon svg-icon-4 me-1">
              <inline-svg src="media/icons/duotune/arrows/arr063.svg" />
            </span>
            Back
          </button>

          <button
            type="button"
            class="btn btn-lg btn-light-primary me-3"
            v-if="modalId == 'update'"
            @click="handleSave"
          >
            <span v-if="!loading" class="indicator-label"> Save </span>
            <span v-if="loading" class="indicator-label">
              Please wait...
              <span
                class="spinner-border spinner-border-sm align-middle ms-2"
              ></span>
            </span>
          </button>
          <button
            type="button"
            class="btn btn-lg btn-primary align-self-end"
            @click="handleSubmit"
            v-else
          >
            <span v-if="!loading" class="indicator-label">
              Create Appointment
              <span class="svg-icon svg-icon-4 ms-1 me-0">
                <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
              </span>
            </span>
            <span v-if="loading" class="indicator-label">
              Please wait...
              <span
                class="spinner-border spinner-border-sm align-middle ms-2"
              ></span>
            </span>
          </button>
        </div>
      </el-form>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType, ref, watchEffect } from "vue";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";
import { useStore } from "vuex";
import { FormRulesMap } from "element-plus/es/components/form/src/form.type";
import AppointmentHistory from "@/components/presets/PatientElements/AppointmentHistory.vue";
import {
  IAptInfoData,
  IOtherInfoData,
} from "@/assets/ts/components/_CreateAppointmentComponent";

export default defineComponent({
  components: { AppointmentHistory, InputWrapper },

  props: {
    modalId: {
      type: String,
      required: true,
    },
    aptInfoDataE: {
      type: Object as PropType<IAptInfoData>,
      required: true,
    },
    patientStatus: {
      type: String,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },

    otherDataE: {
      type: Object as PropType<IOtherInfoData>,
      required: true,
    },
  },
  emits: ["save", "process", "goBack"],

  setup(props, { emit }) {
    const store = useStore();
    const rules = ref<FormRulesMap>({});
    const formRef = ref<null | HTMLFormElement>(null);
    const aneAnswers = ref([]);

    const otherInfoData = ref<IOtherInfoData>({
      anesthetic_questions: false,
      anesthetic_answers: [],
      doctor_address_book_name: "",
      doctor_address_book_id: -1,
      referral_duration: "",
      referral_date: "",
      no_referral: false,
      no_referral_reason: "",
    });

    const aneQuestions = computed(() => store.getters.getAneQuestionActiveList);
    const doctorAddressBooks = computed(
      () => store.getters.getDoctorAddressBookList
    );
    const patientAptData = computed(() => store.getters.getPatientAppointments);

    watchEffect(() => {
      if (props.otherDataE) {
        for (let key in props.otherDataE)
          otherInfoData.value[key] = props.otherDataE[key];
      }
    });

    const handleSelectDoctorAddressBook = (item) => {
      otherInfoData.value.doctor_address_book_id = item.id;
    };

    const searchDoctorAddressBook = (term, cb) => {
      let timeout;
      const results = term
        ? doctorAddressBooks.value.filter(createDoctorAddressBookFilter(term))
        : doctorAddressBooks.value;

      clearTimeout(timeout);
      timeout = setTimeout(() => {
        cb(results);
      }, 1000);
    };

    const createDoctorAddressBookFilter = (term) => {
      const keyword = term.toString();
      return (doctorAddressBook) => {
        const full_name =
          doctorAddressBook.title +
          " " +
          doctorAddressBook.first_name +
          " " +
          doctorAddressBook.last_name;
        const full_name_pos = full_name
          .toLowerCase()
          .indexOf(keyword.toLowerCase());
        const address_pos = doctorAddressBook.practice_address
          .toLowerCase()
          .indexOf(keyword.toLowerCase());
        return full_name_pos !== -1 || address_pos !== -1;
      };
    };

    const handleAneQuestions = () => {
      let temp: number[] = [];
      for (let i in aneAnswers.value) {
        if (aneAnswers.value[i] === true) {
          temp.push(aneQuestions.value[i].id);
        }
      }
      otherInfoData.value.anesthetic_answers = temp;
    };

    const previousStep = () => {
      emit("goBack");
    };

    const handleSave = () => {
      emit("save", otherInfoData.value, 4);
    };

    const handleSubmit = async () => {
      if (!formRef.value) {
        return;
      }
      formRef.value.validate((valid) => {
        if (valid) {
          emit("process", otherInfoData.value);
        }
      });
    };

    const isAnesthetistRequired = computed(() => {
      const id = props.aptInfoDataE.appointment_type_id;
      if (id) {
        const aptTypeList = store.getters.getAptTypesList;
        const selectedType = aptTypeList.filter(
          (aptType) => aptType.id === id
        )[0];
        if (selectedType.anesthetist_required) return true;
      }
      return false;
    });

    return {
      rules,
      formRef,
      aneAnswers,
      otherInfoData,
      aneQuestions,
      isAnesthetistRequired,
      handleSelectDoctorAddressBook,
      searchDoctorAddressBook,
      handleAneQuestions,
      patientAptData,
      previousStep,
      handleSave,
      handleSubmit,
    };
  },
});
</script>
