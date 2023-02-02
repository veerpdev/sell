<template>
  <ModalWrapper
    title="Select Report Template"
    modalId="report"
    modalRef="reportModal"
    class="modal fade"
    aria-hidden="true"
  >
    <el-form @submit.prevent="submit()" ref="formRef">
      <InputWrapper label="Report Template">
        <el-select
          class="w-100"
          v-model="reportTemplate"
          placeholder="Select Report Template"
        >
          <el-option
            v-for="(option, idx) in reportTemplatesData"
            :key="option.id"
            :value="idx"
            :label="option.title"
          />
        </el-select>
      </InputWrapper>

      <InputWrapper label="Appointment">
        <el-select
          class="w-100"
          v-model="appointment"
          placeholder="Select Appointment"
        >
          <el-option
            v-for="(option, idx) in appointmentsData"
            :key="option.id"
            :value="idx"
            :label="option.appointment_type_name"
          />
        </el-select>
      </InputWrapper>

      <button
        :data-kt-indicator="loading ? 'on' : null"
        class="btn btn-lg btn-primary m-6"
        type="submit"
        data-bs-dismiss="modal"
      >
        <span v-if="!loading" class="indicator-label"> Select </span>
        <span v-if="loading" class="indicator-progress">
          Please wait...
          <span
            class="spinner-border spinner-border-sm align-middle ms-2"
          ></span>
        </span>
      </button>
    </el-form>
  </ModalWrapper>
</template>

<script>
import { defineComponent, ref, watchEffect, computed } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { Mutations } from "@/store/enums/StoreEnums";

export default defineComponent({
  name: "report-modal",
  components: {},
  props: {},
  setup() {
    const router = useRouter();
    const store = useStore();
    const reportTemplatesData = computed(
      () => store.getters.getDocumentTemplateList
    );
    const patientData = computed(() => store.getters.selectedPatient);
    const loading = ref(false);
    const reportTemplate = ref();
    const appointment = ref();
    const appointmentsData = ref([]);
    const reportModal = ref(null);

    watchEffect(() => {
      appointmentsData.value = patientData.value.appointments;
    });

    const submit = () => {
      let selected_report = {
        template: reportTemplatesData.value[reportTemplate.value],
        appointment: appointmentsData.value[appointment.value],
        headerFooter: null,
      };
      store.commit(Mutations.SET_DOCUMENT_TEMPLATES.SELECT, selected_report);

      router.push({ name: "patients-report" });
    };

    return {
      loading,
      reportModal,
      reportTemplate,
      reportTemplatesData,
      appointment,
      appointmentsData,
      submit,
    };
  },
});
</script>
