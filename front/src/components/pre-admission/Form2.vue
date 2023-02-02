<template>
  <el-form
    @submit.prevent="submit()"
    :model="formData"
    :rules="rules"
    ref="formRef"
    class="col-xl-6 mx-auto"
  >
    <CardSection class="text-center">
      <img
        :src="patientData.organization_logo"
        alt="Organization Logo"
        class="mw-300px mx-auto px-6"
      />
    </CardSection>
    <CardSection heading="Appointment Information">
      <table class="table table-flush fw-bold gy-1">
        <tbody>
          <tr>
            <td class="text-muted min-w-125px w-125px w-md-150px">
              Specialist
            </td>
            <td class="text-gray-800">
              <template v-if="aptData.specialist_name">
                <label>{{ aptData.specialist_name }}</label>
              </template>
            </td>
          </tr>
          <tr>
            <td class="text-muted min-w-125px w-125px w-md-150px">
              Appointment Type
            </td>
            <td class="text-gray-800 text-capitalize">
              <template v-if="patientData.appointment_type">
                <label>{{ patientData.appointment_type.name }} </label>
              </template>
            </td>
          </tr>
          <tr>
            <td class="text-muted min-w-125px w-125px w-md-150px">
              Date and Time
            </td>
            <td class="text-gray-800">
              <label>{{ aptData.date + " " + aptData.start_time }}</label>
            </td>
          </tr>
          <tr>
            <td class="text-muted min-w-125px w-125px w-md-150px">Clinic</td>
            <td class="text-gray-800">
              <template v-if="patientData.clinic">
                <label>{{ patientData.clinic }}</label>
              </template>
            </td>
          </tr>
        </tbody>
      </table></CardSection
    >
    <CardSection heading="Your Information">
      <div class="row justify-content-md-center">
        <InputWrapper :class="colString" label="Title" prop="title">
          <el-select
            class="w-100"
            v-model="formData.title"
            placeholder="Select Title"
          >
            <el-option
              v-for="item in titles"
              :key="item.value"
              :value="item.value"
              :label="item.label"
            />
          </el-select>
        </InputWrapper>

        <InputWrapper :class="colString" label="First Name" prop="first_name">
          <el-input
            type="text"
            v-model="formData.first_name"
            placeholder="First Name"
          />
        </InputWrapper>

        <InputWrapper :class="colString" label="Last Name" prop="last_name">
          <el-input
            type="text"
            v-model="formData.last_name"
            placeholder="Last Name"
          />
        </InputWrapper>

        <InputWrapper
          :class="colString"
          label="Date Of Birth"
          prop="date_of_birth"
        >
          <el-date-picker
            class="w-100"
            v-model="formData.date_of_birth"
            format="YYYY-MM-DD"
            placeholder="1990-01-01"
          />
        </InputWrapper>

        <InputWrapper
          :class="colString"
          label="Contact Number"
          prop="contact_number"
        >
          <el-input
            type="text"
            v-mask="'0#-####-####'"
            v-model="formData.contact_number"
            placeholder="Contact Number"
          />
        </InputWrapper>

        <InputWrapper :class="colString" label="Email" prop="email">
          <el-input type="email" v-model="formData.email" placeholder="Email" />
        </InputWrapper>

        <InputWrapper :class="colString" label="Address" prop="address">
          <el-input
            type="text"
            v-model="formData.address"
            placeholder="Address"
          />
        </InputWrapper>

        <InputWrapper :class="colString" label="Occupation" prop="occupation">
          <el-input
            type="text"
            v-model="formData.occupation"
            placeholder="Occupation"
          />
        </InputWrapper>

        <span :class="colString"></span>
      </div>
    </CardSection>

    <CardSection heading="Next Of Kin">
      <div class="row justify-content-md-center">
        <InputWrapper :class="colString" label="Name" prop="kin_name">
          <el-input
            type="text"
            v-model="formData.kin_name"
            placeholder="Kin First Name"
          />
        </InputWrapper>
        <InputWrapper
          :class="colString"
          label="Phone Number"
          prop="kin_phone_number"
        >
          <el-input
            type="text"
            v-mask="'0#-####-####'"
            v-model="formData.kin_phone_number"
            placeholder="Kin Phone Number"
          />
        </InputWrapper>
        <InputWrapper
          :class="colString"
          label="Kin Relationship"
          prop="kin_relationship"
        >
          <el-input
            type="text"
            v-model="formData.kin_relationship"
            placeholder="Kin Relationship"
          />
        </InputWrapper>
        <span :class="colString"></span>
      </div>
    </CardSection>
    <CardSection heading="Billing Details">
      <div class="row justify-content-md-center">
        <InputWrapper
          class="col-12 col-sm-6"
          label="Medicare Card Number"
          prop="medicare_number"
        >
          <el-input
            type="text"
            v-mask="'##########'"
            v-model="formData.medicare_number"
            placeholder="Medicare Card Number"
          />
        </InputWrapper>
        <InputWrapper
          class="col-12 col-sm-6"
          label="Individual Reference Number"
          prop="medicare_reference_number"
        >
          <el-input
            type="text"
            v-mask="'#'"
            v-model="formData.medicare_reference_number"
            placeholder="Individual Reference Number"
          />
        </InputWrapper>
      </div>
    </CardSection>

    <template v-if="patientData.appointment_type?.type === 'PROCEDURE'">
      <div class="separator separator-dashed mb-6"></div>

      <template
        v-for="section in patientData.pre_admission_sections"
        :key="section.id"
      >
        <CardSection :heading="section.title">
          <template v-for="question in section.questions" :key="question.id">
            <InputWrapper :label="question.text">
              <el-input
                v-if="question.answer_format === 'TEXT'"
                v-model="
                  pre_admission_answers[
                    's' + section.id.toString() + '/q' + question.id.toString()
                  ]
                "
              />
              <el-radio-group
                v-if="question.answer_format === 'BOOLEAN'"
                v-model="
                  pre_admission_answers[
                    's' + section.id.toString() + '/q' + question.id.toString()
                  ]
                "
              >
                <el-radio label="Yes" size="large">Yes</el-radio>
                <el-radio label="No" size="large">No</el-radio>
              </el-radio-group>
            </InputWrapper>
          </template>
        </CardSection>
      </template>
    </template>

    <div class="separator separator-dashed mb-6"></div>

    <CardSection heading="Consent">
      <div
        v-if="patientData.pre_admission_consent"
        v-html="patientData.pre_admission_consent"
      ></div>

      <el-checkbox
        type="checkbox"
        :label="
          'I, ' +
          formData.first_name +
          ' ' +
          formData.last_name +
          ', agree to the conditions stated above.'
        "
      />
    </CardSection>

    <div class="d-flex justify-content-end mb-5 me-5 gap-5">
      <button
        :data-kt-indicator="loading ? 'on' : null"
        class="btn btn-lg btn-primary"
        type="submit"
      >
        <span v-if="!loading" class="indicator-label"> Confirm </span>
        <span v-if="loading" class="indicator-progress">
          Please wait...
          <span
            class="spinner-border spinner-border-sm align-middle ms-2"
          ></span>
        </span>
      </button>
      <button type="reset" class="btn btn-light-primary w-min-250px">
        Cancel
      </button>
    </div>
    <!--end::Card body-->
    <!--end::details View-->
  </el-form>
</template>

<script>
import { defineComponent, ref, onMounted, computed, watch } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import maritalStatus from "@/core/data/marital-status";
import titles from "@/core/data/titles";
import race from "@/core/data/race";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";

import { mask } from "vue-the-mask";
import { validatePhone } from "@/helpers/helpers";

export default defineComponent({
  name: "pre-admission-form2",
  directives: {
    mask,
  },
  components: {
    InputWrapper,
  },
  data: function () {
    return {
      colString: "col-12 col-sm-6 col-xl-4",
    };
  },
  setup() {
    const store = useStore();
    const router = useRouter();
    const formRef = ref(null);
    const patientData = computed(() => store.getters.getAptPreAdmissionOrg);
    const pre_admission_answers = ref([]);
    const apt_id = ref("");
    const html2Pdf = ref("");
    const formData = ref({
      title: "",
      first_name: "",
      last_name: "",
      date_of_birth: "",
      contact_number: "",
      email: "",
      address: "",
      occupation: "",
      kin_name: "",
      kin_phone_number: "",
      kin_relationship: "",
      medicare_number: "",
      medicare_reference_number: "",
    });
    const loading = ref(false);

    const aptData = ref({
      specialist_name: "",
      date: "",
      start_time: "",
    });

    const rules = ref({
      first_name: [
        {
          required: true,
          message: "First Name cannot be blank",
          trigger: "change",
        },
      ],
      last_name: [
        {
          required: true,
          message: "Last Name cannnot be blank",
          trigger: "change",
        },
      ],
      contact_number: [
        {
          required: true,
          message: "Contact Number cannot be blank",
          trigger: "change",
        },
        { validator: validatePhone, trigger: "blur" },
      ],
      email: [
        {
          required: true,
          message: "Email cannot be blank",
          trigger: "change",
        },
        {
          type: "email",
          message: "Please input correct email address",
          trigger: ["blur", "change"],
        },
      ],
      address: [
        {
          required: true,
          message: "Address cannot be blank",
          trigger: "change",
        },
      ],
      kin_name: [
        {
          required: true,
          message: "Kin Name cannot be blank",
          trigger: "change",
        },
      ],
      kin_phone_number: [
        {
          required: true,
          message: "Kin Number cannnot be blank",
          trigger: "change",
        },
        { validator: validatePhone, trigger: "blur" },
      ],
      kin_relationship: [
        {
          required: true,
          message: "Kin Relationship cannot be blank",
          trigger: "change",
        },
      ],
    });

    const formattedAnswer = () => {
      const res = [];
      patientData.value.pre_admission_sections.forEach((section) => {
        const sec = {
          id: section.id,
          organization_id: section.organization_id,
          section_title: section.title,
          section_questions: [],
        };
        section.questions.forEach((question) => {
          const _question =
            pre_admission_answers.value[
              "s" + section.id.toString() + "/q" + question.id.toString()
            ];
          const que = {
            id: question.id,
            pre_admission_section_id: question.pre_admission_section_id,
            question_text: question.text,
            question_answer: _question,
          };
          sec.section_questions.push(que);
        });
        res.push(sec);
      });
      return res;
    };

    const submit = () => {
      loading.value = true;
      if (!formRef.value) {
        loading.value = false;
        return;
      }

      formRef.value.validate(async (valid) => {
        if (valid) {
          formData.value.apt_id = apt_id.value;
          formData.value.pre_admission_answers = JSON.stringify(
            formattedAnswer()
          );

          store.dispatch(
            AppointmentActions.PRE_ADMISSION.STORE,
            formData.value
          );
          loading.value = false;
          router.push({
            path:
              "/appointment_pre_admissions/show/" + apt_id.value + "/form_3",
          });
        } else {
          loading.value = false;
        }
      });
    };

    watch(patientData, () => {
      for (let key in formData.value)
        formData.value[key] = patientData.value.patient[key];
      for (let key in aptData.value)
        aptData.value[key] = patientData.value.appointment[key];
    });

    onMounted(async () => {
      setCurrentPageBreadcrumbs("Administration", ["Patients"]);
      apt_id.value = router.currentRoute.value.params.id.toString();
      await store.dispatch(
        AppointmentActions.PRE_ADMISSION.ORGANIZATION,
        apt_id.value
      );
    });

    watch(patientData, () => {
      if (
        patientData.value.status !== "BOOKED" &&
        patientData.value.status !== "VALIDATED"
      ) {
        router.push({
          path: "/appointment_pre_admissions/show/" + apt_id.value + "/form_1",
        });
      }
    });

    return {
      aptData,
      formData,
      formRef,
      rules,
      titles,
      maritalStatus,
      patientData,
      html2Pdf,
      loading,
      pre_admission_answers,
      submit,
      race,
    };
  },
});
</script>
