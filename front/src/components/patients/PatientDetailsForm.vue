<template>
  <el-form
    @submit.prevent="submit()"
    :model="formData"
    :rules="rules"
    ref="formRef"
  >
    <HeadingText text="Patient Details" />

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
        <div class="el-input">
          <GMapAutocomplete
            :value="formData.address"
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

      <InputWrapper :class="colString" label="Post Code" prop="postcode">
        <el-input
          disabled
          type="text"
          v-model="formData.postcode"
          placeholder="Post Code"
        />
      </InputWrapper>

      <InputWrapper :class="colString" label="Gender" prop="gender">
        <el-select
          class="w-100"
          v-model="formData.gender"
          placeholder="Select Gender"
        >
          <el-option
            v-for="item in genders"
            :key="item.value"
            :value="item.value"
            :label="item.label"
          />
        </el-select>
      </InputWrapper>
      <InputWrapper
        :class="colString"
        label="Marital Status"
        prop="marital_status"
      >
        <el-select
          class="w-100"
          v-model="formData.marital_status"
          placeholder="Marital Status"
        >
          <el-option
            v-for="status in maritalStatus"
            :key="status.value"
            :value="status.value"
            :label="status.label"
          />
        </el-select>
      </InputWrapper>

      <InputWrapper :class="colString" label="Occupation" prop="occupation">
        <el-input
          type="text"
          v-model="formData.occupation"
          placeholder="Occupation"
        />
      </InputWrapper>

      <InputWrapper :class="colString" label="Race" prop="race">
        <el-select class="w-100" v-model="formData.race" placeholder="Race">
          <el-option
            v-for="item in race"
            :key="item.value"
            :value="item.value"
            :label="item.label"
          />
        </el-select>
      </InputWrapper>

      <InputWrapper
        :class="colString"
        label="Country Of Birth"
        prop="country_of_birth"
      >
        <el-select
          filterable
          class="w-100"
          v-model="formData.country_of_birth"
          placeholder="Country Of Birth"
        >
          <el-option
            v-for="item in country_of_birth"
            :key="item.value"
            :value="item.value"
            :label="item.label"
          />
        </el-select>
      </InputWrapper>
      <span :class="colString"></span>
      <span :class="colString"></span>
    </div>

    <el-divider />
    <div
      class="d-flex flex-row mb-5 align-items-center justify-content-between"
    >
      <HeadingText class="align-items-center" text="Next Of Kin" />

      <el-checkbox
        prop="kin_receive_correspondence"
        type="checkbox"
        class="pb-5"
        v-model="formData.kin_receive_correspondence"
        label="NOK to receive copies of correspondence"
      />
    </div>
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
    </div>
    <div class="row justify-content-md-left">
      <InputWrapper :class="colString" label="Email" prop="kin_email">
        <el-input
          type="email"
          v-model="formData.kin_email"
          placeholder="Kin Email"
        />
      </InputWrapper>
      <span :class="colString"></span>
    </div>

    <div class="d-flex justify-content-end">
      <button
        v-if="showCancel"
        type="button"
        @click.prevent="cancel"
        class="btn btn-light m-3"
      >
        Cancel
      </button>

      <button
        type="submit"
        :data-kt-indicator="loading ? 'on' : null"
        class="btn btn-primary m-3"
      >
        <span v-if="!loading" class="indicator-label">
          {{ buttonText }}
        </span>
        <span v-if="loading" class="indicator-progress">
          Please wait...
          <span
            class="spinner-border spinner-border-sm align-middle ms-2"
          ></span>
        </span>
      </button>
    </div>
  </el-form>
</template>
<script lang="ts">
import { defineComponent, ref, PropType, watch, computed } from "vue";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import maritalStatus from "@/core/data/marital-status";
import titles from "@/core/data/titles";
import genders from "@/core/data/genders";
import race from "@/core/data/race";
import country_of_birth from "@/core/data/patient-birth-country";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";
import { mask } from "vue-the-mask";
import { validatePhone } from "@/helpers/helpers";
import IPatient from "@/store/interfaces/IPatient";
import store from "@/store";
import moment from "moment";

export default defineComponent({
  name: "patient-details-form",
  directives: {
    mask,
  },
  components: { InputWrapper },
  data: function () {
    return {
      colString: "col-12 col-sm-6 col-lg-4 ",
      colActionString: "col-1 col-sm-1 col-lg-1 ",
    };
  },
  props: {
    patient: {
      required: true,
      type: Object as PropType<IPatient>,
    },
    buttonText: {
      required: false,
      type: String,
      default: "Save",
    },
    onSubmitExtras: {
      required: false,
      type: Function,
    },
    onCancel: {
      required: false,
      type: Function,
    },
    create: {
      required: false,
      type: Boolean,
      default: false,
    },
  },
  setup(props) {
    const formRef = ref<null | HTMLFormElement>(null);
    const formData = ref<IPatient>(props.patient);
    const loading = ref<boolean>(false);
    const showCancel = computed(() => props.onCancel);

    watch(props, () => {
      formData.value = props.patient;
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
          message: "Last Name cannot be blank",
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
          message: "Kin Number cannot be blank",
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
      kin_email: [
        {
          required: true,
          message: "Kin Email cannot be blank",
          trigger: "change",
        },
        {
          type: "email",
          message: "Please input correct email address",
          trigger: ["blur", "change"],
        },
      ],
    });

    const formatDate = (date) => {
      return moment(date).format("YYYY-MM-DD").toString();
    };

    const handleAddressChange = (e) => {
      formData.value.address = e.formatted_address;
      const postCodeData = e.address_components.filter((data) => {
        return data.types.some((type) => type === "postal_code");
      });
      if (postCodeData && postCodeData.length > 0) {
        formData.value.postcode = postCodeData[0].short_name;
      } else {
        formData.value.postcode = "";
      }
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      let action = PatientActions.UPDATE;

      if (props.create) {
        action = PatientActions.CREATE;
      }

      const submitData = {
        ...formData.value,
        date_of_birth: formatDate(formData.value.date_of_birth),
      };

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(action, submitData)
            .then(() => {
              if (props.onSubmitExtras) {
                props.onSubmitExtras();
              }
            })
            .finally(() => {
              loading.value = false;
            });
        }
      });
    };

    const cancel = () => {
      console.log("Sean");
      if (props.onCancel) {
        props.onCancel();
      }
    };

    return {
      formData,
      formRef,
      rules,
      titles,
      genders,
      race,
      country_of_birth,
      maritalStatus,
      handleAddressChange,
      submit,
      cancel,
      showCancel,
      loading,
    };
  },
});
</script>
