<template>
  <CardSection>
    <PatientDetailsForm
      :patient="patient"
      :on-submit-extras="onUpdateDetails"
    />
  </CardSection>
  <PatientAlsoKnownAs :patient="patient" />
</template>
<style lang="scss">
.action-width {
  width: 60px;
}
</style>
<script lang="ts">
import { defineComponent, ref, onMounted, watchEffect } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { PatientActions } from "@/store/enums/StorePatientEnums";

import IPatient from "@/store/interfaces/IPatient";
import { useRoute } from "vue-router";
import PatientAlsoKnownAs from "@/components/patients/PatientAlsoKnownAs.vue";
import PatientDetailsForm from "@/components/patients/PatientDetailsForm.vue";
import CardSection from "@/components/presets/GeneralElements/CardSection.vue";
import { displaySuccessToast } from "@/helpers/helpers";
export default defineComponent({
  name: "patient-administration",
  components: { PatientAlsoKnownAs, PatientDetailsForm, CardSection },
  data: function () {
    return {
      colString: "col-12 col-sm-6 col-lg-4 ",
      colActionString: "col-1 col-sm-1 col-lg-1 ",
    };
  },
  setup() {
    const route = useRoute();
    const store = useStore();
    const patient = ref<IPatient>(store.getters.selectedPatient);

    watchEffect(() => {
      patient.value = store.getters.selectedPatient;
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("Demographic", ["Patients"]);
      const id = route.params.id;
      store.dispatch(PatientActions.VIEW, id);
    });

    const onUpdateDetails = () => {
      displaySuccessToast("Updated patient details");
    };

    return {
      patient,
      onUpdateDetails,
    };
  },
});
</script>
