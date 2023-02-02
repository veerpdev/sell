<template>
  <ModalWrapper
    :title="title"
    :modalId="customId"
    modalRef="patientMedicationRef"
    :is-static="true"
  >
    <el-form
      @submit.prevent
      :model="formData"
      :rules="rules"
      ref="patientMedicationFormRef"
    >
      <div class="row justify-content-md-center">
        <InputWrapper label="Name" prop="name">
          <el-input type="text" v-model="formData.name" placeholder="Name" />
        </InputWrapper>

        <InputWrapper label="Instructions" prop="instructions">
          <el-input
            type="text"
            v-model="formData.instructions"
            placeholder="Instructions"
          />
        </InputWrapper>

        <div class="d-flex px-0 justify-content-between">
          <InputWrapper label="Start Date" prop="start_date" class="w-50">
            <el-date-picker
              editable
              class="w-100"
              v-model="formData.start_date"
              format="DD-MM-YYYY"
              placeholder="Select Start Date"
            />
          </InputWrapper>

          <InputWrapper label="End Date" prop="end_date" class="w-50">
            <el-date-picker
              editable
              class="w-100"
              v-model="formData.end_date"
              format="DD-MM-YYYY"
              placeholder="Select End Date"
            />
          </InputWrapper>
        </div>
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
import AlertBadge from "@/components/presets/GeneralElements/AlertBadge.vue";
import IPatientMedication from "@/store/interfaces/IPatientMedication";
import IPatient from "@/store/interfaces/IPatient";
import { Modal } from "bootstrap";
import moment from "moment";

export default defineComponent({
  name: "patient-medication-modal",
  props: {
    patient: { required: true, type: Object as PropType<IPatient> },
    medication: Object as PropType<IPatientMedication>,
    shouldEmit: { type: Boolean, default: false },
    modalId: { type: String },
  },
  emits: ["closeModal"],
  components: {
    AlertBadge,
  },
  setup(props, { emit }) {
    const store = useStore();
    const medication = computed<null | IPatientMedication>(() => {
      return props.medication ? props.medication : null;
    });
    const parentModal = ref<Modal>(null);
    const patientMedicationFormRef = ref<null | HTMLFormElement>(null);
    const patientMedicationRef = ref<null | HTMLElement>(null);
    const loading = ref<boolean>(false);
    const patient = computed<IPatient>(() => props.patient);
    const validated = ref(null);
    const validationMessage = ref(null);
    const customId = computed<string>(
      () => props.modalId ?? "patient_medication"
    );
    const title = computed<string>(() =>
      medication.value ? "Update Medication" : "Add Medication"
    );
    const isDataUnchanged = ref<boolean>(medication.value ? true : false);
    const formData = ref({
      name: "",
      instructions: "",
      start_date: "",
      end_date: "",
    });

    const rules = ref({
      name: [
        {
          required: true,
          message: "Name cannot be blank",
          trigger: "change",
        },
      ],
      instructions: [
        {
          required: true,
          message: "Instructions cannot be blank",
          trigger: "change",
        },
      ],
      start_date: [
        {
          required: true,
          message: "Start date cannot be blank",
          trigger: "change",
        },
      ],
      end_date: [
        {
          required: true,
          message: "End date cannot be blank",
          trigger: "change",
        },
      ],
    });

    const closeModal = () => {
      if (medication.value) isDataUnchanged.value = true;
      emit("closeModal");
    };

    const handleClick = () => {
      medication.value ? updateMedication() : addNewMedication();
    };

    const addNewMedication = () => {
      const medicationSource = JSON.parse(JSON.stringify(formData))._value;
      loading.value = true;
      medicationSource.patient_id = patient.value.id;
      medicationSource.start_date = moment(medicationSource.start_date)
        .format("YYYY-MM-DD")
        .toString();
      medicationSource.end_date = moment(medicationSource.end_date)
        .format("YYYY-MM-DD")
        .toString();
      store
        .dispatch(PatientActions.MEDICATION.ADD, medicationSource)
        .then(() => {
          closeModal();
        })
        .finally(() => {
          loading.value = false;
        });
    };

    const updateMedication = () => {
      loading.value = true;
      const data = {
        id: medication?.value?.id,
        patient_id: patient.value.id,
        ...formData.value,
      };

      data.start_date = moment(data.start_date).format("YYYY-MM-DD").toString();
      data.end_date = moment(data.end_date).format("YYYY-MM-DD").toString();

      store
        .dispatch(PatientActions.MEDICATION.UPDATE, data)
        .then(() => {
          closeModal();
        })
        .finally(() => {
          loading.value = false;
          isDataUnchanged.value = true;
        });
    };

    const resetForm = () => {
      if (medication.value) isDataUnchanged.value = true;
      patientMedicationFormRef?.value?.resetFields();
    };

    watch(
      () => formData,
      () => {
        validated.value = null;
        validationMessage.value = null;
        if (medication.value) {
          for (let key in formData.value) {
            if (formData.value[key] != medication.value[key])
              isDataUnchanged.value = false;
          }
        }
      },
      { deep: true }
    );

    watch(
      () => medication,
      () => {
        resetForm();

        formData.value.name = medication?.value?.name ?? "";
        formData.value.instructions = medication?.value?.instructions ?? "";
        formData.value.start_date = medication?.value?.start_date ?? "";
        formData.value.end_date = medication?.value?.end_date ?? "";
      },
      { deep: true }
    );

    onMounted(() => {
      if (medication.value) isDataUnchanged.value = true;
      parentModal.value = document.getElementById(`modal_${customId.value}`);
      parentModal.value.addEventListener("hidden.bs.modal", function () {
        resetForm();
      });
    });

    return {
      loading,
      patientMedicationFormRef,
      patientMedicationRef,
      validated,
      validationMessage,
      formData,
      rules,
      addNewMedication,
      customId,
      title,
      handleClick,
    };
  },
});
</script>
