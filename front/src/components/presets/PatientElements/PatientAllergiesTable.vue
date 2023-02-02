<template>
  <CardSection class="col-md-6 col-sm-12" heading="Allergies">
    <template #header-actions>
      <IconButton label="Add Allergy" @click.prevent="showAllergyModal(null)" />
    </template>

    <template #default>
      <Datatable
        v-if="tableData != undefined"
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="5"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-name="{ row: allergy }">
          {{ allergy.name }}
        </template>

        <template v-slot:cell-severity="{ row: allergy }">
          {{ allergy.severity }}
        </template>

        <template v-slot:cell-symptoms="{ row: allergy }">
          {{ allergy.symptoms }}
        </template>

        <template v-slot:cell-actions="{ row: allergy }">
          <div class="d-flex">
            <button
              class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
              @click.prevent="showAllergyModal(allergy)"
            >
              <span class="svg-icon svg-icon-3">
                <InlineSVG icon="pencil" />
              </span>
            </button>

            <button
              class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
              @click="handleDeleteAllergy(allergy)"
            >
              <span class="svg-icon svg-icon-3">
                <InlineSVG icon="bin" />
              </span>
            </button>
          </div>
        </template>
      </Datatable>

      <PatientAllergyModal
        :patient="selectedPatient"
        :allergy="updatingAllergy"
        v-on:closeModal="closePatientAllergyModal"
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
import PatientAllergyModal from "@/views/patients/modals/PatientAllergyModal.vue";
import { Modal } from "bootstrap";
import IPatientAllergy from "@/store/interfaces/IPatientAllergy";
import IPatient from "@/store/interfaces/IPatient";

export default defineComponent({
  name: "patient-allergies-table",
  props: {
    patient: { required: true, type: Object },
  },
  components: {
    PatientAllergyModal,
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
        name: "Severity",
        key: "severity",
        sortable: true,
      },
      {
        name: "Symptoms",
        key: "symptoms",
        sortable: false,
      },
      {
        name: "Actions",
        key: "actions",
        sortable: false,
      },
    ]);
    const tableData = ref<IPatientAllergy[]>();
    const updatingAllergy = ref<IPatientAllergy>();
    const patientAllergyModal = ref<Modal>();

    const showAllergyModal = (source) => {
      updatingAllergy.value = source;

      if (!patientAllergyModal.value) {
        patientAllergyModal.value = new Modal(
          document.getElementById("modal_patient_allergy")
        );
      }
      patientAllergyModal.value.show();
    };

    const handleDeleteAllergy = (item) => {
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
          deleteAllergy(item);
        }
      });
    };

    const deleteAllergy = (item) => {
      store.dispatch(PatientActions.MEDICATION.DELETE, item);
    };

    const closePatientAllergyModal = () => {
      patientAllergyModal.value.hide();
    };

    onMounted(() => {
      const id = route.params.id;
      store.dispatch(PatientActions.VIEW, id);
      setCurrentPageBreadcrumbs("Clinical Information", ["Patients"]);

      const modal = document.getElementById("modal_patient_allergy");

      modal?.addEventListener("hidden.bs.modal", function () {
        updatingAllergy.value = undefined;
      });
    });

    watchEffect(() => {
      tableData.value = props.patient.allergies ?? [];
    });

    return {
      selectedPatient,
      loading,
      tableHeader,
      tableData,
      handleDeleteAllergy,
      showAllergyModal,
      updatingAllergy,
      closePatientAllergyModal,
    };
  },
});
</script>
