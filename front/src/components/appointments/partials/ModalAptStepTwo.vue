<template>
  <div data-kt-stepper-element="content">
    <div class="w-100">
      <!--       START SEARCH EXISTING PATIENT-->
      <el-form
        class="w-100"
        :model="filterPatient"
        v-if="!isNewPatient && modalId === 'new' && patientStep === 1"
        @submit.prevent=""
      >
        <div class="d-flex flex-column">
          <InputWrapper label="First Name" prop="filter_first_name">
            <el-input
              type="text"
              v-model="filterPatient.first_name"
              placeholder="First Name"
            />
          </InputWrapper>
          <InputWrapper label="Last Name" prop="filter_last_name">
            <el-input
              type="text"
              v-model="filterPatient.last_name"
              placeholder="Last Name"
            />
          </InputWrapper>
          <InputWrapper label="Date of Birth" prop="filter_date">
            <el-date-picker
              editable
              class="w-100"
              v-model="filterPatient.date_of_birth"
              format="DD-MM-YYYY"
              placeholder="01-01-1990"
            />
          </InputWrapper>

          <div class="d-flex justify-content-between my-auto">
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
              class="btn btn-lg btn-primary align-self-end"
              v-if="modalId === 'new'"
              @click="patientStep_1"
            >
              Search
              <span class="svg-icon svg-icon-4 ms-1 me-0">
                <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
              </span>
            </button>
          </div>
          <div v-if="modalId == 'update'">
            <button
              type="button"
              class="btn btn-lg btn-light-primary me-3"
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
              type="submit"
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-lg btn-primary align-self-end"
            >
              Continue
              <span class="svg-icon svg-icon-4 ms-1 me-0">
                <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
              </span>
            </button>
          </div>
        </div>
        <!--END SEARCH EXISTING PATIENT-->
        <!--begin::Separator-->
      </el-form>
      <!--      START SELECT A EXISTING PATIENT-->
      <el-form
        v-if="patientStep === 2 && modalId === 'new'"
        class="w-100"
        @submit.prevent=""
      >
        <div class="row scroll h-300px">
          <Datatable
            :table-header="patientTableHeader"
            :table-data="patientTableData"
            :key="tableKey"
            :rows-per-page="5"
            :enable-items-per-page-dropdown="true"
          >
            <template v-slot:cell-UR_number="{ row: item }">
              {{ item.UR_number }}
            </template>
            <template v-slot:cell-full_name="{ row: item }">
              <span class="text-dark fw-bolder text-hover-primary mb-1 fs-6">
                {{ item.first_name }} {{ item.last_name }}
              </span>
            </template>
            <template v-slot:cell-dob="{ row: item }">
              <span class="text-dark fw-bolder text-hover-primary mb-1 fs-6">
                {{ formatDate(item.date_of_birth) }}
              </span>
            </template>
            <template v-slot:cell-contact_number="{ row: item }">
              <span class="text-dark fw-bolder text-hover-primary mb-1 fs-6">
                {{ item.contact_number }}
              </span>
            </template>
            <template v-slot:cell-action="{ row: item }">
              <button
                @click="selectPatient(item)"
                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
              >
                <span class="svg-icon svg-icon-3">
                  <em class="fas fa-check"></em>
                </span>
              </button>
            </template>
          </Datatable>
        </div>
        <span v-if="patientInfoData.is_ok === false">
          This patient is blacklisted and cannot be booked in. Please speak to
          your organization manager to resolve.
        </span>
        <div class="special-patient-alerts d-flex gap-2 flex-row">
          <template v-for="alert in patientInfoData.alerts" :key="alert.id">
            <template v-if="!alert.is_dismissed">
              <PatientAlert :alert="alert" />
              <ViewPatientAlertModal :alert="alert" />
            </template>
          </template>
        </div>
        <div class="d-flex justify-content-between">
          <button
            type="button"
            class="btn btn-lg btn-light-primary me-3"
            data-kt-stepper-action="previous"
            @click="patientPrevStep"
          >
            <span class="svg-icon svg-icon-4 me-1">
              <inline-svg src="media/icons/duotune/arrows/arr063.svg" />
            </span>
            Back
          </button>
          <button
            type="button"
            v-if="patientInfoData.is_ok"
            class="btn btn-lg btn-primary align-self-end"
            @click="afterSelectPatient"
          >
            Continue
            <span class="svg-icon svg-icon-4 ms-1 me-0">
              <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
            </span>
          </button>
        </div>
      </el-form>
      <!--      END SELECT A EXISTING PATIENT-->
      <!--      START CREATE A NEW PATIENT ||  edit existing patient-->
      <el-form
        v-if="patientStep === 3"
        class="w-100"
        :model="patientInfoData"
        :rules="rules"
        ref="formRef"
        @submit.prevent=""
      >
        <div class="row scroll h-500px">
          <InputWrapper
            required
            class="col-6"
            label="First Name"
            prop="first_name"
          >
            <el-input
              type="text"
              v-model="patientInfoData.first_name"
              @keyup="matchExistPatientHandle(event)"
              placeholder="Enter First Name"
            />
          </InputWrapper>
          <InputWrapper
            required
            class="col-6"
            label="Last Name"
            prop="last_name"
          >
            <el-input
              type="text"
              v-model="patientInfoData.last_name"
              @keyup="matchExistPatientHandle(event)"
              placeholder="Enter Last Name"
            />
          </InputWrapper>

          <InputWrapper
            required
            class="col-6"
            label="Date of Birth"
            prop="date_of_birth"
          >
            <el-date-picker
              editable
              class="w-100"
              format="DD-MM-YYYY"
              v-model="patientInfoData.date_of_birth"
              @change="matchExistPatientHandle(event)"
              placeholder=""
            />
          </InputWrapper>
          <InputWrapper
            required
            class="col-6"
            label="Contact Number"
            prop="contact_number"
          >
            <el-input
              type="text"
              v-mask="'0#-####-####'"
              v-model="patientInfoData.contact_number"
              placeholder="Enter Contact Number"
            />
          </InputWrapper>

          <div
            class="exist-message px-7 mt-2 mb-2"
            v-if="patientInfoData.is_exist"
          >
            <label class="mb-2">
              A patient matching these details already exists
            </label>
            <button
              type="button"
              class="btn btn-lg btn-primary w-100 mb-5"
              @click="showMatchPatientsHandle"
            >
              Show match patients
            </button>
          </div>

          <InputWrapper label="Address" prop="address">
            <div class="el-input">
              <GMapAutocomplete
                :value="patientInfoData.address"
                ref="addressRef"
                placeholder="Enter the Address"
                @place_changed="handleAddressChange"
                :options="{
                  componentRestrictions: {
                    country: 'au',
                  },
                }"
              >
              </GMapAutocomplete>
            </div>
          </InputWrapper>

          <InputWrapper class="col-6" label="Email" prop="email">
            <el-input
              type="text"
              v-model="patientInfoData.email"
              placeholder="Enter Email"
            />
          </InputWrapper>

          <InputWrapper
            class="col-6"
            label="Confirm Method"
            prop="appointment_confirm_method"
          >
            <el-select
              class="w-100"
              v-model="patientInfoData.appointment_confirm_method"
              placeholder="Confirm Method"
            >
              <el-option value="sms" label="SMS" />
              <el-option value="email" label="Email" />
            </el-select>
          </InputWrapper>

          <InputWrapper
            class="col-12"
            label="Clinical Alerts"
            prop="clinical_alerts"
          >
            <el-input
              type="textarea"
              v-model="patientInfoData.clinical_alerts"
              placeholder="Enter Clinical Alerts"
            />
          </InputWrapper>
          <InputWrapper class="col-12" label="Allergies" prop="allergies">
            <el-select
              class="w-100"
              multiple
              filterable
              allow-create
              default-first-option
              :reserve-keyword="false"
              v-model="patientInfoData.allergies"
            >
              <el-option
                v-for="item in allergiesList"
                :value="item.id"
                :label="item.name"
                :key="item.id"
              />
            </el-select>
          </InputWrapper>
        </div>
        <div class="d-flex justify-content-between">
          <button
            type="button"
            class="btn btn-lg btn-light-primary me-3"
            data-kt-stepper-action="previous"
            @click="patientPrevStep"
          >
            <span class="svg-icon svg-icon-4 me-1">
              <inline-svg src="media/icons/duotune/arrows/arr063.svg" />
            </span>
            Back
          </button>
          <div>
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
            >
              Continue
              <span class="svg-icon svg-icon-4 ms-1 me-0">
                <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
              </span>
            </button>
          </div>
        </div>
      </el-form>
      <!--      END CREATE A NEW PATIENT-->
    </div>
  </div>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  PropType,
  reactive,
  ref,
  watch,
  watchEffect,
} from "vue";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";
import { useStore } from "vuex";
import { FormRulesMap } from "element-plus/es/components/form/src/form.type";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import moment from "moment";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { validatePhone } from "@/helpers/helpers";
import PatientAlert from "@/components/presets/PatientElements/PatientAlert.vue";
import ViewPatientAlertModal from "@/views/patients/modals/ViewPatientAlertModal.vue";
import { mask } from "vue-the-mask";
import chargeTypes from "@/core/data/charge-types";
import {
  IBillingInfoData,
  IPatientInfoData,
} from "@/assets/ts/components/_CreateAppointmentComponent";

export default defineComponent({
  components: {
    InputWrapper,
    Datatable,
    PatientAlert,
    ViewPatientAlertModal,
  },

  directives: {
    mask,
  },

  props: {
    modalId: {
      type: String,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },
    isNewPatient: {
      type: Boolean,
      required: true,
    },
    patientStatus: {
      type: String,
      required: true,
    },
    patientDataE: {
      type: Object as PropType<IPatientInfoData>,
      required: true,
    },
  },
  emits: ["save", "process", "goBack", "updatePatient"],

  setup(props, { emit }) {
    const store = useStore();

    const rules = ref<FormRulesMap>({
      first_name: [
        {
          required: true,
          message: "First name cannot be blank.",
          trigger: "blur",
        },
      ],
      last_name: [
        {
          required: true,
          message: "Last name cannot be blank.",
          trigger: "blur",
        },
      ],
      date_of_birth: [
        {
          required: true,
          message: "Date of birth cannot be blank.",
          trigger: "blur",
        },
      ],
      email: [
        {
          type: "email",
          message: "Please input correct email address",
          trigger: "blur",
        },
      ],
      contact_number: [
        {
          required: true,
          message: "Mobile Number cannot be blank.",
          trigger: "blur",
        },
        { validator: validatePhone, trigger: "blur" },
      ],
      appointment_confirm_method: [
        {
          required: true,
          message: "Appointment confirm cannot be blank.",
          trigger: "blur",
        },
      ],
    });

    const filterPatient = reactive({
      first_name: "",
      last_name: "",
      date_of_birth: "",
      ur_number: "",
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
      alerts: [],
      is_ok: false,
    });

    const billingInfoData = ref<IBillingInfoData>({
      charge_type: chargeTypes[0].value,
      claim_sources: [],
      procedure_price: 0,
      add_other_account_holder: false,
    });

    const patientTableHeader = ref([
      {
        name: "Full Name",
        key: "full_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Date of Birth",
        key: "dob",
        sortable: true,
        searchable: true,
      },
      {
        name: "Contact Number",
        key: "contact_number",
        sortable: true,
        searchable: true,
      },
      {
        name: "",
        key: "action",
      },
    ]);

    const patientStep = ref(1);
    const formRef = ref<null | HTMLFormElement>(null);
    const patientTableData = ref<unknown>([]);
    const allergiesList = ref<unknown>([]);
    const tableKey = ref<number>(0);

    const patientList = computed(() => store.getters.patientsList);

    watchEffect(() => {
      if (props.patientDataE) {
        for (let key in props.patientDataE)
          patientInfoData.value[key] = props.patientDataE[key];
      }
    });

    watch(patientList, () => {
      patientTableData.value = patientList.value;
      renderTable();
    });

    watch(props, () => {
      if (props.patientStatus === "new") patientStep.value = 3;
      else {
        patientStep.value = 1;
        filterPatient.first_name = "";
        filterPatient.last_name = "";
        filterPatient.date_of_birth = "";
      }
    });

    const handleSubmit = async () => {
      if (!formRef.value) {
        return;
      }
      formRef.value.validate((valid) => {
        if (valid) {
          emit("process", patientInfoData.value, billingInfoData.value);
        }
      });
    };

    const handleSave = () => {
      console.log("Save existing appointment");
      emit("save", patientInfoData.value, 2);
    };

    const previousStep = () => {
      emit("goBack");
    };

    const showMatchPatientsHandle = () => {
      patientStep.value = 1;
      filterPatient.first_name = patientInfoData.value.first_name;
      filterPatient.last_name = patientInfoData.value.last_name;
      filterPatient.date_of_birth = patientInfoData.value.date_of_birth;
      patientStep_1();
      patientInfoData.value.is_exist = false;
    };

    const matchExistPatientHandle = () => {
      let filtered_patients = patientList.value.filter(
        (p) =>
          p.first_name === patientInfoData.value.first_name &&
          p.last_name === patientInfoData.value.last_name &&
          moment(p.date_of_birth).format("DD/MM/YYYY") ===
            moment(patientInfoData.value.date_of_birth).format("DD/MM/YYYY")
      );
      if (filtered_patients.length) {
        patientInfoData.value.is_exist = true;
      }
    };

    const afterSelectPatient = () => {
      patientStep.value++;
    };

    // Search existing patients base on given params
    const patientStep_1 = () => {
      patientTableData.value = [];
      for (let key in patientInfoData.value) patientInfoData.value[key] = "";
      if (filterPatient.date_of_birth !== "")
        filterPatient.date_of_birth = moment(
          filterPatient.date_of_birth
        ).format("YYYY-MM-DD");
      store.dispatch(PatientActions.LIST, filterPatient);
      patientStep.value++;
    };

    const renderTable = () => tableKey.value++;

    const handleAddressChange = (e) => {
      patientInfoData.value.address = e.formatted_address;
    };

    const formatDate = (date) => {
      return moment(date).format("DD-MM-YYYY").toString();
    };

    const patientPrevStep = () => {
      if (props.patientStatus === "new") previousStep();
      else if (props.modalId === "update") previousStep();
      else patientStep.value--;
    };

    // SELECT PATIENT FROM KTD TABLE
    const selectPatient = (item) => {
      store.dispatch(PatientActions.APPOINTMENTS, item.id);
      store.dispatch(PatientActions.VIEW, item.id);
      for (let key in patientInfoData.value)
        patientInfoData.value[key] = item[key];
      patientInfoData.value.alerts = item.alerts;

      // Update selected patient id in aptInfoData
      emit("updatePatient", item.id);

      for (let key in billingInfoData.value) {
        if (
          key === "charge_type" ||
          key === "procedure_price" ||
          key === "claim_sources"
        ) {
          continue;
        }

        billingInfoData.value[key] = item[key];
      }

      billingInfoData.value.claim_sources = item.billings;

      patientInfoData.value.also_known_as = item.also_known_as;

      patientInfoData.value.is_ok = true;
      let blocklist = patientInfoData.value.alerts.filter(
        (a) => a.alert_level == "BLACKLISTED" && !a.is_dismissed
      );
      if (blocklist.length) patientInfoData.value.is_ok = false;
      // patientStep.value++;
    };

    return {
      rules,
      formRef,
      filterPatient,
      patientTableHeader,
      patientStep,
      previousStep,
      patientStep_1,
      handleSave,
      patientTableData,
      tableKey,
      patientInfoData,
      showMatchPatientsHandle,
      matchExistPatientHandle,
      afterSelectPatient,
      handleAddressChange,
      formatDate,
      allergiesList,
      patientPrevStep,
      handleSubmit,
      selectPatient,
    };
  },
});
</script>
<style lang="scss">
.special-patient-alerts .modal.patient-alert .modal-footer {
  display: none;
}

.exist-message {
  label {
    color: grey;
  }
}
</style>
