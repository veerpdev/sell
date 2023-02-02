<template>
  <!--begin::Content-->
  <div class="d-flex flex-row-fluid flex-center bg-white rounded">
    <!--begin::Form-->
    <!--begin::Step 1-->
    <div class="current" data-kt-stepper-element="content">
      <div class="w-100 py-20 px-9">
        <el-form
          class="w-100 w-xl-700px w-xxl-800px"
          :rules="rules"
          :model="formData"
          ref="formRef"
        >
          <div class="container py-6">
            <div class="row justify-content-md-center">
              <InputWrapper
                required
                :class="colString"
                label="Clinic Name"
                prop="name"
              >
                <el-input
                  v-model="formData.name"
                  type="text"
                  placeholder="Clinic Name"
                />
              </InputWrapper>
              <InputWrapper
                required
                :class="colString"
                label="Clinic Nickname"
                prop="nickname_code"
              >
                <el-input
                  v-model="formData.nickname_code"
                  type="text"
                  placeholder="e.g BAW"
                />
              </InputWrapper>
              <InputWrapper
                required
                :class="colString"
                label="Reception Email"
                prop="email"
              >
                <el-input
                  v-model="formData.email"
                  type="text"
                  placeholder="Email"
                />
              </InputWrapper>

              <InputWrapper
                required
                :class="colString"
                label="Address"
                prop="address"
              >
                <GMapAutocomplete
                  ref="addressRef"
                  :value="formData.address"
                  placeholder="Enter the Address"
                  @place_changed="handleAddressChange"
                  :options="{
                    componentRestrictions: {
                      country: 'au',
                    },
                  }"
                >
                </GMapAutocomplete>
              </InputWrapper>

              <InputWrapper
                required
                :class="colString"
                label="Reception number"
                prop="phone_number"
              >
                <el-input
                  type="text"
                  v-mask="'0#-####-####'"
                  v-model="formData.phone_number"
                  placeholder="Phone Number"
                />
              </InputWrapper>

              <InputWrapper
                :class="colString"
                label="Reception Fax"
                prop="fax_number"
              >
                <el-input
                  type="text"
                  v-mask="'0#-####-####'"
                  v-model="formData.fax_number"
                  placeholder="Fax Number"
                />
              </InputWrapper>
            </div>
            <el-divider />
          </div>
          <div class="container py-6">
            <div class="row justify-content-md-center">
              <InputWrapper
                :class="colString"
                label="Provider Number"
                prop="hospital_provider_number"
              >
                <el-input
                  required
                  v-model="formData.hospital_provider_number"
                  v-mask="'#######A'"
                  type="text"
                  placeholder="Provider Number"
                />
              </InputWrapper>

              <InputWrapper
                :class="colString"
                label="VAED number"
                prop="VAED_number"
              >
                <el-input
                  v-model="formData.VAED_number"
                  type="text"
                  placeholder="VAED number"
                />
              </InputWrapper>

              <InputWrapper :class="colString" label="LSPN id" prop="lspn_id">
                <el-input
                  v-model="formData.lspn_id"
                  type="text"
                  placeholder="LSPN id"
                />
              </InputWrapper>
              <InputWrapper
                :class="colString"
                label="Health Link"
                prop="healthlink_edi"
              >
                <el-input
                  v-model="formData.healthlink_edi"
                  type="text"
                  placeholder="Health Link"
                />
              </InputWrapper>
              <InputWrapper
                :class="colString"
                label="Specimen Collection Point"
                prop="specimen_collection_point_number"
              >
                <el-input
                  v-model="formData.specimen_collection_point_number"
                  type="text"
                  placeholder="Specimen Collection Point"
                />
              </InputWrapper>

              <InputWrapper :class="colString" label="Minor ID" prop="minor_id">
                <el-input
                  v-model="formData.minor_id"
                  type="text"
                  placeholder="Minor ID"
                />
              </InputWrapper>

              <InputWrapper :class="colString">
                <button
                  type="button"
                  class="btn btn-md btn-primary mt-1 w-100"
                  :data-kt-indicator="loadingMinorId ? 'on' : null"
                  :disabled="loadingMinorId"
                  @click="getMinorId"
                >
                  <span class="indicator-label">Get Minor ID</span>
                  <span class="indicator-progress">
                    Please wait...
                    <span
                      class="spinner-border spinner-border-sm align-middle ms-2"
                    ></span>
                  </span>
                </button>
              </InputWrapper>
            </div>
            <el-divider />
            <button
              type="button"
              class="btn btn-lg btn-primary me-3"
              data-kt-stepper-action="submit"
              @click="submit()"
            >
              <span class="indicator-label">
                {{ formInfo.submitButtonName }}
              </span>
              <span class="indicator-progress">
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
    <!--end::Step 1 -->
  </div>
</template>
<script>
import {
  defineComponent,
  onMounted,
  ref,
  reactive,
  watch,
  computed,
} from "vue";
import { useStore } from "vuex";
import Swal from "sweetalert2/dist/sweetalert2.min.js";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { Actions } from "@/store/enums/StoreEnums";
import { useRouter, useRoute } from "vue-router";
import { countryList, timeZoneList } from "@/core/data/country";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";

import { mask } from "vue-the-mask";
import { validatePhone } from "@/helpers/helpers";

export default defineComponent({
  name: "create-clinic",
  directives: {
    mask,
  },
  components: { InputWrapper },
  data: function () {
    return {
      colString: "col-12 col-sm-6  ",
    };
  },
  setup() {
    const store = useStore();
    const router = useRouter();
    const route = useRoute();
    const formRef = ref(null);
    const addressRef = ref(null);

    const formInfo = reactive({
      isCreate: true,
      title: "Create Clinic",
      submitAction: Actions.CLINICS.CREATE,
      submitButtonName: "Create",
      submittedText: "New Clinic Created",
    });
    const loading = ref(false);
    const loadingMinorId = ref(false);
    const formData = ref({
      name: "",
      nickname_code: "",
      email: "",
      phone_number: "",
      fax_number: "",
      VAED_number: "",
      minor_id: "",
      hospital_provider_number: "",
      address: "",
      lspn_id: "",
      specimen_collection_point_number: "",
      healthlink_edi: "",
    });
    const rules = ref({
      name: [
        {
          required: true,
          message: "Clinic Name cannot be blank.",
          trigger: "change",
        },
      ],
      nickname_code: [
        {
          required: true,
          message: "Please enter a nickname code.",
          trigger: "change",
        },
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
      phone_number: [
        {
          required: true,
          message: "Phone Number cannot be blank.",
          trigger: "change",
        },
        { validator: validatePhone, trigger: "blur" },
      ],
      hospital_provider_number: [
        {
          message: "Provider Number cannot be blank",
          trigger: "change",
        },
        {
          min: 7,
          message: "Provider Number must be at least 6 characters",
          trigger: "blur",
        },
      ],
      address: [
        {
          required: true,
          message: "Address cannot be blank",
          trigger: "change",
        },
      ],
    });

    const createClinicsRef = ref(null);
    const currentStepIndex = ref(0);
    const uploadDisabled = ref(false);
    const upload = ref(null);
    const Data = new FormData();
    const dialogImageUrl = ref("");
    const dialogVisible = ref(false);
    const clinicsList = computed(() => store.getters.clinicsList);
    const currentUser = computed(() => store.getters.currentUser);

    const handleChange = (file, fileList) => {
      upload.value.clearFiles();
      uploadDisabled.value = false;
      Data.append("footnote_signature", file.raw);
      uploadDisabled.value = fileList.length >= 1;
    };

    const handleRemove = (file, fileList) => {
      uploadDisabled.value = fileList.length - 1;
    };

    const handlePreview = (uploadFile) => {
      dialogImageUrl.value = uploadFile.url;
      dialogVisible.value = true;
    };

    const handleAddressChange = (e) => {
      formData.value.address = e.formatted_address;
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }
      loading.value = true;

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store.dispatch(formInfo.submitAction, formData.value).then((data) => {
            loading.value = false;
            data && router.push({ name: "clinics" });
          });
        }
      });
    };

    const getMinorId = () => {
      const clinicData = {
        name: formData.value.name,
        location_id: formData.value?.id ?? null,
        organization_id: currentUser.value.profile.organization_id,
      };

      loadingMinorId.value = true;

      store
        .dispatch(Actions.CLINICS.MINOR_ID, clinicData)
        .then(({ minor_id }) => {
          formData.value.minor_id = minor_id;
        })
        .catch((error) => {
          console.log("Error:", error);
        })
        .finally(() => {
          loadingMinorId.value = false;
        });
    };

    watch(clinicsList, () => {
      const id = route.params.id;

      clinicsList.value.forEach((item) => {
        if (item.id == id) {
          Object.assign(formData.value, item);
        }
      });

      setCurrentPageBreadcrumbs(formInfo.title, ["Clinic"]);
    });

    onMounted(() => {
      const id = route.params.id;

      if (id != undefined) {
        formInfo.title = "Edit Clinic";
        formInfo.isCreate = false;
        formInfo.submitButtonName = "Update";
        formInfo.submittedText = "Clinic Updated";
        formInfo.submitAction = Actions.CLINICS.UPDATE;
      }

      store.dispatch(Actions.CLINICS.LIST);
    });

    return {
      formData,
      formInfo,
      rules,
      submit,
      formRef,
      addressRef,
      loading,
      createClinicsRef,
      currentStepIndex,
      countryList,
      timeZoneList,
      upload,
      handleChange,
      handleRemove,
      handlePreview,
      dialogVisible,
      dialogImageUrl,
      handleAddressChange,
      loadingMinorId,
      getMinorId,
    };
  },
});
</script>
