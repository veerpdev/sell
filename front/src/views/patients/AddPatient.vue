<template>
  <CardSection>
    <PatientDetailsForm
      :patient="formData"
      :on-cancel="backToPatientList"
      :on-submit-extras="backToPatientList"
      button-text="Add Patient"
      create
    />
  </CardSection>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import PatientDetailsForm from "@/components/patients/PatientDetailsForm.vue";
import CardSection from "@/components/presets/GeneralElements/CardSection.vue";
import IPatient from "@/store/interfaces/IPatient";

export default defineComponent({
  name: "add-patients-screen",
  components: {
    CardSection,
    PatientDetailsForm,
  },
  setup() {
    const router = useRouter();
    const formData = ref({} as IPatient);

    const backToPatientList = () => {
      router.push({ name: "patients" });
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Add Patient", ["Patients"]);
    });

    return {
      formData,
      backToPatientList,
    };
  },
});
</script>
