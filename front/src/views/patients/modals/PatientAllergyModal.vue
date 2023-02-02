<template>
  <ModalWrapper
    :title="title"
    :modalId="customId"
    modalRef="patientAllergyRef"
    :is-static="true"
  >
    <el-form
      @submit.prevent
      :model="formData"
      :rules="rules"
      ref="patientAllergyFormRef"
    >
      <div class="row justify-content-md-center">
        <InputWrapper label="Name" prop="name">
          <el-input type="text" v-model="formData.name" placeholder="Name" />
        </InputWrapper>

        <InputWrapper label="Severity" prop="severity">
          <el-select
            class="w-100"
            v-model="formData.severity"
            placeholder="Select Severity"
          >
            <el-option
              v-for="item in patientAllergyTypes"
              :key="item.value"
              :value="item.label"
              :label="item.label"
            />
          </el-select>
        </InputWrapper>

        <InputWrapper label="Symptoms" prop="symptoms">
          <el-input
            type="text"
            v-model="formData.symptoms"
            placeholder="Symptoms"
          />
        </InputWrapper>
      </div>
    </el-form>

    <AlertBadge
      v-if="validationMessage != null"
      :text="validationMessage"
      :color="validated ? 'success' : 'warning'"
      icon=""
    />

    <div class="d-flex justify-content-end">
      <button
        :data-kt-indicator="loading ? 'on' : null"
        class="btn btn-lg btn-primary me-2"
        :disabled="loading || isDataUnchanged"
        @click="handleClick"
      >
        <span v-if="!loading" class="indicator-label">{{ title }}</span>
        <span v-if="loading" class="indicator-progress">
          Please wait...
          <span
            class="spinner-border spinner-border-sm align-middle ms-2"
          ></span>
        </span>
      </button>

      <button
        class="btn btn-lg btn-secondary"
        data-bs-dismiss="modal"
        type="submit"
      >
        Cancel
      </button>
    </div>
  </ModalWrapper>
</template>

<script lang="ts">
import {
  defineComponent,
  ref,
  computed,
  onMounted,
  watch,
  PropType,
} from "vue";
import { useStore } from "vuex";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import Swal from "sweetalert2/dist/sweetalert2.js";
import patientAllergyTypes from "@/core/data/patient-allergy-types";
import AlertBadge from "@/components/presets/GeneralElements/AlertBadge.vue";
import IPatientAllergy from "@/store/interfaces/IPatientAllergy";
import IPatient from "@/store/interfaces/IPatient";
import { Modal } from "bootstrap";

export default defineComponent({
  name: "patient-allergy-modal",
  props: {
    patient: { required: true, type: Object as PropType<IPatient> },
    allergy: Object as PropType<IPatientAllergy>,
    shouldEmit: { type: Boolean, default: false },
    modalId: { type: String },
  },
  emits: ["closeModal"],
  components: {
    AlertBadge,
  },
  setup(props, { emit }) {
    const store = useStore();
    const allergy = computed<null | IPatientAllergy>(() => {
      return props.allergy ? props.allergy : null;
    });
    const parentModal = ref<Modal>(null);
    const patientAllergyFormRef = ref<null | HTMLFormElement>(null);
    const patientAllergyRef = ref<null | HTMLElement>(null);
    const loading = ref<boolean>(false);
    const patient = computed<IPatient>(() => props.patient);
    const validated = ref(null);
    const validationMessage = ref(null);
    const customId = computed<string>(() => props.modalId ?? "patient_allergy");
    const title = computed<string>(() =>
      allergy.value ? "Update Allergy" : "Add Allergy"
    );
    const isDataUnchanged = ref<boolean>(allergy.value ? true : false);
    const formData = ref({
      name: "",
      severity: "",
      symptoms: "",
    });

    const rules = ref({
      name: [
        {
          required: true,
          message: "Name cannot be blank",
          trigger: "change",
        },
      ],
      severity: [
        {
          required: true,
          message: "Must select severity type",
          trigger: "change",
        },
      ],
      symptoms: [
        {
          required: true,
          message: "Symptoms cannot be blank",
          trigger: "change",
        },
      ],
    });

    const closeModal = () => {
      if (allergy.value) isDataUnchanged.value = true;
      emit("closeModal");
    };

    const handleClick = () => {
      allergy.value ? updateAllergy() : addNewAllergy();
    };

    const addNewAllergy = () => {
      const allergySource = JSON.parse(JSON.stringify(formData))._value;
      loading.value = true;
      allergySource.patient_id = patient.value.id;
      store
        .dispatch(PatientActions.ALLERGY.ADD, allergySource)
        .then(() => {
          closeModal();
        })
        .finally(() => {
          loading.value = false;
        });
    };

    const updateAllergy = () => {
      loading.value = true;
      const data = {
        id: allergy?.value?.id,
        patient_id: patient.value.id,
        ...formData.value,
      };

      store
        .dispatch(PatientActions.ALLERGY.UPDATE, data)
        .then(() => {
          closeModal();
        })
        .finally(() => {
          loading.value = false;
          isDataUnchanged.value = true;
        });
    };

    const resetForm = () => {
      if (allergy.value) isDataUnchanged.value = true;
      patientAllergyFormRef?.value?.resetFields();
    };

    watch(
      () => formData,
      () => {
        validated.value = null;
        validationMessage.value = null;
        if (allergy.value) {
          for (let key in formData.value) {
            if (formData.value[key] != allergy.value[key])
              isDataUnchanged.value = false;
          }
        }
      },
      { deep: true }
    );

    watch(
      () => allergy,
      () => {
        resetForm();

        formData.value.name = allergy?.value?.name ?? "";
        formData.value.severity = allergy?.value?.severity ?? "";
        formData.value.symptoms = allergy?.value?.symptoms ?? "";
      },
      { deep: true }
    );

    onMounted(() => {
      if (allergy.value) isDataUnchanged.value = true;
      parentModal.value = document.getElementById(`modal_${customId.value}`);
      parentModal.value.addEventListener("hidden.bs.modal", function () {
        resetForm();
      });
    });

    return {
      loading,
      patientAllergyFormRef,
      patientAllergyRef,
      validated,
      validationMessage,
      formData,
      patientAllergyTypes,
      rules,
      addNewAllergy,
      customId,
      title,
      handleClick,
    };
  },
});
</script>
