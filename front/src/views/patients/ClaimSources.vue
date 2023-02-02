<template>
  <CardSection heading="Claim Sources">
    <template #header-actions>
      <IconButton label="Add Claim Source" @click="handleAddClaimSource" />
    </template>

    <template #default>
      <Datatable
        :table-header="tableHeader"
        :table-data="tableData"
        :key="tableKey"
        :rows-per-page="5"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-billing_type="{ row: item }">
          {{ getBillingType(item.billing_type) }}
          <span v-if="item.billing_type == 2" class="text-primary">
            <br />
            {{ getHealthFund(item.health_fund_id) }}
          </span>

          <template v-if="item.has_medicare_concession">
            <br />
            <span class="badge badge-info opacity-50 mx-2"> CONCESSION </span>
          </template>
        </template>

        <template v-slot:cell-member_number="{ row: item }">
          {{ item.member_number }}
        </template>

        <template v-slot:cell-ref_number="{ row: item }">
          {{ item.member_reference_number ?? "N/A" }}
        </template>

        <template v-slot:cell-validated="{ row: item }">
          <div>
            {{
              item.verified_at
                ? moment(item.verified_at).format("DD-MM-YYYY")
                : "Never"
            }}

            <span
              v-if="enableMedicareValidation && item.verified_at"
              class="badge opacity-50 mx-2"
              :class="{
                'badge-success': item.is_valid,
                'badge-danger': !item.is_valid,
              }"
            >
              {{ item.is_valid ? "Valid" : "Invalid" }}
            </span>
          </div>

          <button
            v-if="enableMedicareValidation"
            class="btn btn-bg-light btn-active-color-primary btn-sm mt-2"
            @click="revalidateSource(item)"
            :disabled="loading != null"
            :data-kt-indicator="loading == item.id ? 'on' : 'off'"
          >
            <span class="indicator-label">Re-validate</span>

            <span class="indicator-progress">
              Validating...
              <span
                class="spinner-border spinner-border-sm align-middle ms-2"
              ></span>
            </span>
          </button>
        </template>

        <template v-slot:cell-actions="{ row: item }">
          <div class="d-flex flex-column">
            <button
              v-if="enableMedicareValidation && item.billing_type == 1"
              class="btn btn-bg-light btn-active-color-primary btn-sm mt-2"
              :disabled="loading != null || !item.is_valid"
              @click="handleCheckConcession(item)"
              :data-kt-indicator="loading == `${item.id}-Con` ? 'on' : 'off'"
            >
              <!-- Only show this button if it's a Medicare source -->
              <span class="indicator-label">Check Concession</span>

              <span class="indicator-progress">
                Validating...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>

            <button
              class="btn btn-bg-light btn-active-color-primary btn-sm mt-2"
              :disabled="loading != null"
              @click="handleUpdateClaimSource(item)"
            >
              Update Details
            </button>

            <button
              class="btn btn-bg-danger btn-sm mt-2"
              :disabled="loading != null"
              @click="handleDeleteSource(item)"
            >
              Delete Source
            </button>
          </div>
        </template>
      </Datatable>

      <AddClaimSourceModal
        :patient="selectedPatient"
        v-on:closeModal="closeAddClaimSourceModal"
        v-on:updateDetails="updatePatientDetails"
      />

      <UpdateClaimSourceModal
        :patient="selectedPatient"
        :claimSource="updatingSource"
        v-on:closeModal="closeUpdateClaimSourceModal"
        v-on:updateDetails="updatePatientDetails"
      />
    </template>
  </CardSection>
</template>

<script>
import { defineComponent, ref, watchEffect, onMounted, computed } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { Actions } from "@/store/enums/StoreEnums";
import moment from "moment";
import Swal from "sweetalert2/dist/sweetalert2.js";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import AddClaimSourceModal from "@/views/patients/modals/AddClaimSourceModal.vue";
import UpdateClaimSourceModal from "@/views/patients/modals/UpdateClaimSourceModal.vue";
import IconButton from "@/components/presets/GeneralElements/IconButton.vue";
import { Modal } from "bootstrap";
import PatientBillingTypes from "@/core/data/patient-billing-types";
import { isMedicareValidationEnabled } from "@/core/data/billing";

export default defineComponent({
  name: "patient-claim-sources",
  components: {
    AddClaimSourceModal,
    UpdateClaimSourceModal,
    IconButton,
    Datatable,
  },
  data: function () {
    return {
      colString: "col-12 col-sm-6 ",
    };
  },
  setup() {
    const store = useStore();
    const route = useRoute();
    const healthFundsList = computed(() => store.getters.healthFundsList);
    const selectedPatient = computed(() => store.getters.selectedPatient);
    const minorId = computed(() => store.getters.latestMinorId);
    const enableMedicareValidation = computed(() =>
      isMedicareValidationEnabled()
    );
    const loading = ref(null);
    const tableHeader = ref([
      {
        name: "Source Type",
        key: "billing_type",
        sortable: true,
      },
      {
        name: "Member Number",
        key: "member_number",
        sortable: false,
      },
      {
        name: "Reference Number",
        key: "ref_number",
        sortable: false,
      },
      {
        name: "Last Validated",
        key: "validated",
        sortable: false,
      },
      {
        name: "Actions",
        key: "actions",
        sortable: false,
      },
    ]);
    const tableData = ref([]);
    const tableKey = ref(0);
    const updateDetails = ref({});
    const updatingSource = ref(null);
    const addClaimSourceModal = ref(null);
    const updateClaimSourceModal = ref(null);

    const renderTable = () => tableKey.value++;

    const getBillingType = (type) => {
      const foundType = PatientBillingTypes.find(
        (billing) => billing.value == type
      );

      return foundType?.label ?? null;
    };

    const getHealthFund = (id) => {
      const foundFund = healthFundsList.value.find((fund) => fund.code == id);

      return foundFund?.name ?? null;
    };

    const handleAddClaimSource = () => {
      if (!addClaimSourceModal.value) {
        addClaimSourceModal.value = new Modal(
          document.getElementById("modal_add_claim_source")
        );
      }

      addClaimSourceModal.value.show();
    };

    const handleUpdateClaimSource = (source) => {
      updatingSource.value = source;

      if (!updateClaimSourceModal.value) {
        updateClaimSourceModal.value = new Modal(
          document.getElementById("modal_update_claim_source")
        );
      }

      updateClaimSourceModal.value.show();
    };

    const handleDeleteSource = (item) => {
      Swal.fire({
        text: `Are you sure you want to delete this ${getBillingType(
          item.billing_type
        )}?`,
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
          deleteSource(item);
        }
      });
    };

    const deleteSource = (item) => {
      store.dispatch(PatientActions.CLAIM_SOURCE.DELETE, item);
    };

    const doValidation = (endpoint, data, item, isConcession = false) => {
      loading.value = isConcession ? `${item.id}-Con` : item.id;
      updateDetails.value = {};

      store
        .dispatch(endpoint, data)
        .then(() => {
          const validation = store.getters.validationResponse;

          if (validation.data.verified) {
            if (isConcession) {
              item.has_medicare_concession = true;
            } else {
              item.is_valid = true;
            }

            Swal.fire({
              title: "Verification successful!",
              icon: "success",
              buttonsStyling: true,
              confirmButtonText: "Okay",
              customClass: {
                confirmButton: "btn btn-primary",
              },
            });
          }

          if (!validation.data.verified) {
            if (isConcession) {
              item.has_medicare_concession = false;
            } else {
              item.is_valid = false;
            }

            Swal.fire({
              title: "Verification unsuccessful",
              text: `Reason: ${validation.data.message}`,
              icon: "error",
              buttonsStyling: true,
              confirmButtonText: "Okay",
              customClass: {
                confirmButton: "btn btn-primary",
              },
            });
          }

          store.dispatch(PatientActions.CLAIM_SOURCE.UPDATE, item);
        })
        .catch((e) => {
          const errors = store.getters.getErrors;
          let message;
          console.log(e);
          if (
            Object.prototype.hasOwnProperty.call(errors, "errors") &&
            errors.errors.length >= 1
          ) {
            message = errors.errors[0];
          } else {
            message = "Unknown Error. Please try again.";
          }

          Swal.fire({
            title: "Verification unsuccessful",
            text: message,
            icon: "error",
            buttonsStyling: true,
            confirmButtonText: "Okay",
            customClass: {
              confirmButton: "btn btn-primary",
            },
          });
        })
        .finally(() => {
          loading.value = null;
        });
    };

    const revalidateSource = (item) => {
      let validationData = {};
      let endpoint;

      if (minorId.value.minorId == null) {
        Swal.fire({
          text: `No Minor ID could be found. Please ensure all clinics have an assigned Minor ID.`,
          icon: "error",
          buttonsStyling: true,
          confirmButtonText: "Okay",
          customClass: {
            confirmButton: "btn btn-primary",
          },
        }).then(() => {
          return;
        });
      }

      switch (item.billing_type) {
        case 1:
          // Medicare card
          validationData = {
            first_name: selectedPatient.value.first_name,
            last_name: selectedPatient.value.last_name,
            date_of_birth: selectedPatient.value.date_of_birth,
            sex: selectedPatient.value.gender,
            medicare_number: item.member_number,
            medicare_reference_number: item.member_reference_number,
            minor_id: minorId.value.minorId,
          };
          endpoint = PatientActions.CLAIM_SOURCE.VALIDATE_MEDICARE;
          break;
        case 2: {
          // Health Fund
          validationData = {
            first_name: selectedPatient.value.first_name,
            last_name: selectedPatient.value.last_name,
            date_of_birth: selectedPatient.value.date_of_birth,
            sex: selectedPatient.value.gender,
            fund_member_number: item.member_number,
            fund_reference_number: item.member_reference_number,
            fund_organisation_code: item.value.health_fund_id,
            minor_id: minorId.value.minorId,
          };
          endpoint = PatientActions.CLAIM_SOURCE.VALIDATE_HEALTH_FUND;
          break;
        }
        case 3:
          // DVA
          validationData = {
            first_name: selectedPatient.value.first_name,
            last_name: selectedPatient.value.last_name,
            date_of_birth: selectedPatient.value.date_of_birth,
            sex: selectedPatient.value.gender,
            veteran_number: item.member_number,
            minor_id: minorId.value.minorId,
          };
          endpoint = PatientActions.CLAIM_SOURCE.VALIDATE_DVA;
          break;
      }

      doValidation(endpoint, validationData, item);
    };

    const handleCheckConcession = (item) => {
      const validationData = {
        first_name: selectedPatient.value.first_name,
        last_name: selectedPatient.value.last_name,
        date_of_birth: selectedPatient.value.date_of_birth,
        medicare_number: item.member_number,
        medicare_reference_number: item.member_reference_number,
        minor_id: minorId.value.minorId,
      };
      const endpoint = PatientActions.CLAIM_SOURCE.VALIDATE_CONCESSION;

      doValidation(endpoint, validationData, item, true);
    };

    const updatePatientDetails = (details) => {
      const previousData = {
        patient_id: selectedPatient.value.id,
        first_name: selectedPatient.value.first_name,
        last_name: selectedPatient.value.last_name,
      };

      const updateData = {
        id: selectedPatient.value.id,
        first_name: selectedPatient.value.first_name,
        last_name: selectedPatient.value.last_name,
        date_of_birth: selectedPatient.value.date_of_birth,
      };

      for (const detailName in details) {
        updateData[detailName] = details[detailName];
      }

      store
        .dispatch(PatientActions.UPDATE, updateData)
        .then(() => {
          store.dispatch(PatientActions.LIST);
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        })
        .finally(() => {
          loading.value = null;
        });

      store
        .dispatch(PatientActions.ALSO_KNOWN_AS.CREATE, previousData)
        .then(() => {
          store.dispatch(PatientActions.LIST);
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        })
        .finally(() => {
          loading.value = null;
        });
    };

    const closeAddClaimSourceModal = () => {
      renderTable();
      addClaimSourceModal.value.hide();
    };

    const closeUpdateClaimSourceModal = () => {
      renderTable();
      updateClaimSourceModal.value.hide();
    };

    onMounted(() => {
      const id = route.params.id;
      store.dispatch(PatientActions.VIEW, id);
      setCurrentPageBreadcrumbs("Claim Sources", ["Patients"]);
      store.dispatch(Actions.HEALTH_FUND.LIST);

      const updateModal = document.getElementById("modal_update_claim_source");
      updateModal.addEventListener("hidden.bs.modal", function () {
        updatingSource.value = null;
        renderTable();
      });
    });

    watchEffect(() => {
      tableData.value = selectedPatient.value?.billings ?? [];
      renderTable();
    });

    return {
      healthFundsList,
      selectedPatient,
      loading,
      tableHeader,
      tableData,
      tableKey,
      PatientBillingTypes,
      getBillingType,
      moment,
      revalidateSource,
      handleDeleteSource,
      handleCheckConcession,
      updateDetails,
      handleAddClaimSource,
      getHealthFund,
      updatingSource,
      handleUpdateClaimSource,
      closeAddClaimSourceModal,
      closeUpdateClaimSourceModal,
      updatePatientDetails,
      enableMedicareValidation,
    };
  },
});
</script>
