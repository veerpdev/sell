<template>
  <CardSection>
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
    >
      <div class="row">
        <InputWrapper
          class="col-sm-6"
          required
          label="Appointment Type Name"
          prop="name"
          :tooltip="helpTexts.forms.appointmentType.name"
        >
          <el-input
            v-model="formData.name"
            type="text"
            placeholder="e.g. Long Consultation"
          />
        </InputWrapper>

        <InputWrapper
          class="col-sm-6"
          label="Type"
          required
          infoTooltip="What type of appointment this is"
          prop="type"
          :tooltip="helpTexts.forms.appointmentType.type"
        >
          <el-select v-model="formData.type" class="w-100">
            <el-option value="CONSULTATION" label="Consultation" />
            <el-option value="PROCEDURE" label="Procedure" />
          </el-select>
        </InputWrapper>

        <InputWrapper
          class="col-sm-4"
          label="Appointment Color"
          required
          prop="type"
          :tooltip="helpTexts.forms.appointmentType.color"
        >
          <div class="d-flex flex-row my-auto gap-2">
            <el-color-picker
              v-model="formData.color"
              class="py-1"
              size="large"
              :predefine="predefineColors"
            />
            <span
              class="text-white px-2"
              :style="{ 'background-color': formData.color }"
            >
              Appointment Color Preview
            </span>
          </div>
        </InputWrapper>

        <!--Input: Collecting Person Required-->
        <InputWrapper
          class="col-sm-4"
          prop="collecting_person_required"
          required
          :tooltip="helpTexts.forms.appointmentType.collecting_person_required"
          label="Collecting Person Required"
        >
          <el-radio-group
            v-model="formData.collecting_person_required"
            size="large"
          >
            <el-radio-button :label="true">Yes</el-radio-button>
            <el-radio-button :label="false">No</el-radio-button>
          </el-radio-group>
        </InputWrapper>
        <!--Input: Anesthetist Required-->
        <InputWrapper
          class="col-sm-4"
          label="Anesthetist Required"
          prop="anesthetist_required"
          required
          :tooltip="helpTexts.forms.appointmentType.anesthetistRequired"
        >
          <el-radio-group v-model="formData.anesthetist_required" size="large">
            <el-radio-button :label="true">Yes</el-radio-button>
            <el-radio-button :label="false">No</el-radio-button>
          </el-radio-group>
        </InputWrapper>

        <!--Input: Arrival Time-->
        <InputWrapper
          class="col-sm-6"
          label="Arrival extra time"
          required
          prop="arrival_time"
          :tooltip="helpTexts.forms.appointmentType.arrivalTime"
        >
          <el-input
            v-model="formData.arrival_time"
            type="number"
            placeholder="Arrival Time"
          />
        </InputWrapper>

        <!--Input: Appointment Length -->
        <InputWrapper
          class="col-sm-6"
          label="Appointment Length"
          required
          prop="appointment_time"
          :tooltip="helpTexts.forms.appointmentType.appointmentTime"
        >
          <el-select v-model="formData.appointment_time" class="w-100">
            <el-option value="SINGLE" label="Single" />
            <el-option value="DOUBLE" label="Double" />
            <el-option value="TRIPLE" label="Triple" />
          </el-select>
        </InputWrapper>

        <InputWrapper
          label="Select Default Billing Items"
          prop="default_items"
          class="col-sm-8"
          :tooltip="helpTexts.forms.appointmentType.defaultBillingItems"
        >
          <el-select
            class="w-100"
            multiple
            filterable
            allow-create
            default-first-option
            :reserve-keyword="false"
            v-model="formData.default_items"
          >
            <el-option
              v-for="item in scheduleItems"
              :value="item.id"
              :label="item.label_name"
              :key="item.id"
            />
          </el-select>
        </InputWrapper>
        <!--Input: Invoice By -->
        <InputWrapper
          class="col-sm-4"
          label="Invoice By"
          required
          prop="invoice_by"
          :tooltip="helpTexts.forms.appointmentType.invoicedBy"
        >
          <el-select v-model="formData.invoice_by" class="w-100">
            <el-option value="CLINIC" label="Clinic" />
            <el-option value="SPECIALIST" label="Specialist" />
          </el-select>
        </InputWrapper>

        <div class="row">
          <!-- Input: Pre Procedure Instructions -->
          <InputWrapper
            v-if="formData?.type == 'PROCEDURE'"
            label="Pre Procedure Instructions"
            prop="pre_procedure_instructions"
            class="col-sm-6"
            :tooltip="helpTexts.forms.appointmentType.preProcedureInstructions"
          >
            <el-form-item prop="body">
              <ckeditor
                :editor="ClassicEditor"
                v-model="formData.pre_procedure_instructions"
              />
            </el-form-item>
          </InputWrapper>

          <!-- Input: Consent -->
          <InputWrapper
            label="Consent"
            prop="consent"
            :class="[formData?.type == 'PROCEDURE' ? 'col-sm-6' : '']"
            :tooltip="helpTexts.forms.appointmentType.consent"
          >
            <el-form-item prop="body">
              <ckeditor :editor="ClassicEditor" v-model="formData.consent" />
            </el-form-item>
          </InputWrapper>
        </div>
      </div>
      <!--end::Scroll-->

      <!--end::Modal body-->

      <!--begin::Modal footer-->
      <div class="modal-footer flex-center">
        <!--begin::Button-->
        <router-link
          type="reset"
          to="/settings/apt-types"
          class="btn btn-light me-3"
        >
          Cancel
        </router-link>
        <!--end::Button-->

        <!--begin::Button-->
        <button
          :data-kt-indicator="loading ? 'on' : null"
          class="btn btn-lg btn-primary"
          type="submit"
        >
          <span v-if="!loading" class="indicator-label">
            {{ formInfo.submitButtonName }}
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
      <!--end::Modal footer-->
    </el-form>
  </CardSection>
</template>

<script lang="ts">
import {
  defineComponent,
  onMounted,
  ref,
  reactive,
  watch,
  computed,
} from "vue";
import { useStore } from "vuex";
import { useRouter, useRoute } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import { Actions } from "@/store/enums/StoreEnums";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";
import ClassicEditor from "ckeditor5-custom-build/build/ckeditor";
import IAppointmentType from "@/store/interfaces/IAppointmentType";
import CardSection from "@/components/presets/GeneralElements/CardSection.vue";
import helpTexts from "@/core/data/help-texts";

export default defineComponent({
  name: "edit-apt-type",
  components: {
    InputWrapper,
    CardSection,
  },
  setup() {
    const store = useStore();
    const router = useRouter();
    const route = useRoute();
    const formRef = ref<HTMLFormElement>();

    const loading = ref<boolean>(false);
    const formData = ref<IAppointmentType>({
      type: "CONSULTATION",
      anesthetist_required: false,
      arrival_time: 15,
      appointment_time: "SINGLE",
      color: "",
      invoice_by: "CLINIC",
      consent: "",
      default_items: [] as Array<number>,
    } as IAppointmentType);
    const reportTemplates = computed(() => store.getters.getReportTemplateList);
    const scheduleItems = computed(() => store.getters.scheduleItemList);
    const aptTypes = computed(() => store.getters.getAptTypesList);
    const predefineColors = ref([
      "#ff4500",
      "#ff8c00",
      "#ffd700",
      "#90ee90",
      "#00ced1",
      "#1e90ff",
      "#c71585",
    ]);

    const formInfo = reactive({
      title: "Create Appointment Type",
      submitAction: AppointmentActions.APPOINTMENT_TYPES.CREATE,
      submitButtonName: "CREATE",
    });

    const rules = ref({
      name: [
        {
          required: true,
          message: "Name cannot be blank",
          trigger: "change",
        },
      ],
      type: [
        {
          required: true,
          message: "A type must be selected",
          trigger: "change",
        },
      ],
      anesthetist_required: [
        {
          required: true,
          message: "A type must be selected",
          trigger: "change",
        },
      ],
      arrival_time: [
        {
          required: true,
          message: "Arrival extra time must not be blank",
          trigger: "change",
        },
      ],
      appointment_time: [
        {
          required: true,
          message: "Appointment length must be selected",
          trigger: "change",
        },
      ],
      color: [
        {
          required: true,
          message: "A type must be selected",
          trigger: "change",
        },
      ],
      invoice_by: [
        {
          required: true,
          message: "An invoice source must be selected",
          trigger: "change",
        },
      ],
      consent: [
        {
          required: false,
          message: "",
          trigger: "change",
        },
      ],
      default_items: [
        {
          required: false,
          message: "",
          trigger: "change",
        },
      ],
    });

    watch(aptTypes, () => {
      const id = route.params.id;

      aptTypes.value.forEach((item) => {
        if (item.id == id) {
          if (item.consent === null) {
            item.consent = "";
          }

          formData.value = item;

          formInfo.title = "Edit Appointment Type";
          formInfo.submitAction = AppointmentActions.APPOINTMENT_TYPES.UPDATE;
          formInfo.submitButtonName = "UPDATE";
        }
      });

      setCurrentPageBreadcrumbs(formInfo.title, ["Settings"]);
    });

    onMounted(() => {
      store.dispatch(AppointmentActions.APPOINTMENT_TYPES.LIST);
      store.dispatch(Actions.DOCUMENT_TEMPLATES.LIST);
      store.dispatch(Actions.SCHEDULE_ITEM.LIST);

      formData.value.color =
        predefineColors.value[
          Math.floor(Math.random() * predefineColors.value.length)
        ];
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;

          store
            .dispatch(formInfo.submitAction, formData.value)
            .then(() => {
              loading.value = false;
              store.dispatch(AppointmentActions.APPOINTMENT_TYPES.LIST);

              if (formRef.value !== undefined) {
                formRef.value.resetFields();
              }

              router.push({ name: "aptTypes" });
            })
            .catch(({ response }) => {
              loading.value = false;
            });
        }
      });
    };

    return {
      formData,
      formInfo,
      rules,
      formRef,
      loading,
      reportTemplates,
      submit,
      scheduleItems,
      predefineColors,
      ClassicEditor,
      helpTexts,
    };
  },
});
</script>
