<template>
  <CardSection>
    <div class="row">
      <PatientAllergiesTable :patient="patient" />
      <PatientMedicationsTable :patient="patient" />
      <PatientMedicalHistoryTable :patient="patient" />
    </div>
  </CardSection>
</template>

<script lang="ts">
import { defineComponent, computed, watch, onMounted } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import CardSection from "@/components/presets/GeneralElements/CardSection.vue";
import PatientAllergiesTable from "../../components/presets/PatientElements/PatientAllergiesTable.vue";
import PatientMedicationsTable from "../../components/presets/PatientElements/PatientMedicationsTable.vue";
import PatientMedicalHistoryTable from "../../components/presets/PatientElements/PatientMedicalHistoryTable.vue";

export default defineComponent({
  name: "patient-clinical",
  components: {
    CardSection,
    PatientAllergiesTable,
    PatientMedicationsTable,
    PatientMedicalHistoryTable,
  },
  setup() {
    const store = useStore();
    const patient = computed(() => store.getters.selectedPatient);
    const route = useRoute();

    onMounted(() => {
      setCurrentPageBreadcrumbs("Clinical Information", ["Patients"]);
      const id = route.params.id;
      store.dispatch(PatientActions.VIEW, id);
    });

    return { patient };
  },
});
</script>
