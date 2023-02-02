<template>
  <CardSection class="col-md-6 col-sm-12" heading="Medications">
    <template #header-actions>
      <IconButton
        label="Add Medication"
        @click.prevent="showPatientMedicationModal(null)"
      />
    </template>

    <template #default>
      <Datatable
        v-if="tableData != undefined"
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="5"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-name="{ row: medication }">
          {{ medication.name }}
        </template>

        <template v-slot:cell-instructions="{ row: medication }">
          {{ medication.instructions }}
        </template>

        <template v-slot:cell-start_date="{ row: medication }">
          {{ medication.start_date }}
        </template>

        <template v-slot:cell-end_date="{ row: medication }">
          {{ medication.end_date }}
        </template>

        <template v-slot:cell-actions="{ row: medication }">
          <div class="d-flex">
            <button
              class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
              @click.prevent="showPatientMedicationModal(medication)"
            >
              <span class="svg-icon svg-icon-3">
                <InlineSVG icon="pencil" />
              </span>
            </button>

            <button
              class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
              @click="handleDeleteMedication(medication)"
            >
              <span class="svg-icon svg-icon-3">
                <InlineSVG icon="bin" />
              </span>
            </button>
          </div>
        </template>
      </Datatable>

      <PatientMedicationModal
        :patient="selectedPatient"
        :medication="updatingMedication"
        v-on:closeModal="closePatientMedicationModal"
      />
    </template>
  </CardSection>
</template>
<script lang="ts">
import { defineComponent, ref, watchEffect, onMounted, computed } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import Swal from "sweetalert2/dist/sweetalert2.js";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import PatientMedicationModal from "@/views/patients/modals/PatientMedicationModal.vue";
import { Modal } from "bootstrap";
import IPatientMedication from "@/store/interfaces/IPatientMedication";
import IPatient from "@/store/interfaces/IPatient";

export default defineComponent({
  name: "patient-medications-table",
  props: {
    patient: { required: true, type: Object },
  },
  components: {
    PatientMedicationModal,
    Datatable,
  },
  setup(props) {
    const store = useStore();
    const route = useRoute();
    const selectedPatient = computed<IPatient>(
      () => store.getters.selectedPatient
    );
    const loading = ref<boolean>(false);
    const tableHeader = ref([
      {
        name: "Name",
        key: "name",
        sortable: true,
      },
      {
        name: "Instructions",
        key: "instructions",
        sortable: false,
      },
      {
        name: "Start date",
        key: "start_date",
        sortable: false,
      },
      {
        name: "End date",
        key: "end_date",
        sortable: false,
      },
      {
        name: "Actions",
        key: "actions",
        sortable: false,
      },
    ]);
    const tableData = ref<IPatientMedication[]>();
    const updatingMedication = ref<IPatientMedication>();
    const patientMedicationModal = ref<Modal>();

    const showPatientMedicationModal = (source) => {
      updatingMedication.value = source;

      if (!patientMedicationModal.value) {
        patientMedicationModal.value = new Modal(
          document.getElementById("modal_patient_medication")
        );
      }
      patientMedicationModal.value.show();
    };

    const handleDeleteMedication = (item) => {
      Swal.fire({
        text: `Are you sure you want to delete this ${item.name}?`,
        icon: "question",
        buttonsStyling: false,
        confirmButtonText: "Yes",
        showCancelButton: true,
        cancelButtonText: "No",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-secondary",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          deleteMedication(item);
        }
      });
    };

    const deleteMedication = (item) => {
      store.dispatch(PatientActions.ALLERGY.DELETE, item);
    };

    const closePatientMedicationModal = () => {
      patientMedicationModal.value.hide();
    };

    onMounted(() => {
      const id = route.params.id;
      store.dispatch(PatientActions.VIEW, id);
      setCurrentPageBreadcrumbs("Clinical Information", ["Patients"]);

      const modal = document.getElementById("modal_patient_medication");

      modal?.addEventListener("hidden.bs.modal", function () {
        updatingMedication.value = undefined;
      });
    });

    watchEffect(() => {
      tableData.value = props.patient.medications ?? [];
    });

    return {
      selectedPatient,
      loading,
      tableHeader,
      tableData,
      handleDeleteMedication,
      showPatientMedicationModal,
      updatingMedication,
      closePatientMedicationModal,
    };
  },
});
</script>
