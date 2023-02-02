<template>
  <div>
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
    >
      <HeadingText text="Patient Details" />

      <div class="row justify-content-md-center">
        <InputWrapper label="Name" prop="charge_type">
          <el-select
            class="w-100"
            v-model="formData.charge_type"
            placeholder="Select Charge Type"
          >
            <el-option
              v-for="type in chargeTypes"
              :key="type.value"
              :value="type.value"
              :label="type.label"
            />
          </el-select>
        </InputWrapper>

        <el-divider />

        <div>
          <button
            type="button"
            class="btn btn-light btn-icon-primary me-3 mb-3"
            @click="showAddClaimSourceModal"
          >
            Add Claim Source
          </button>

          <div
            v-for="(item, index) in selectedPatient.billings"
            :key="`new-claim-source-${index}`"
            class="d-flex flex-row align-items-center justify-content-between p-3 mb-3 card border border-dashed border-primary gap-4"
          >
            <div>
              <label class="fs-5 text-primary">
                {{ getBillingType(item.billing_type) }}:

                <span class="text-black fs-5">
                  {{ item.member_number }}
                </span>
              </label>

              <div v-if="item.billing_type != '3'" class="d-flex gap-3">
                <label class="text-primary">
                  Reference:

                  <span class="text-black">
                    {{ item.member_reference_number ?? "N/A" }}
                  </span>
                </label>

                <label v-if="item.billing_type == '2'" class="text-primary">
                  Fund:

                  <span class="text-black">
                    {{ getHealthFund(item.health_fund_id) }}
                  </span>
                </label>
              </div>
            </div>

            <button
              type="button"
              class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
              @click="deleteClaimSource(item)"
            >
              <span class="svg-icon svg-icon-3">
                <InlineSVG icon="bin" />
              </span>
            </button>
          </div>

          <AddClaimSourceModal
            :patient="selectedPatient"
            :modalId="addClaimSourceModalId"
            v-on:addClaimSource="addNewClaimSource"
            v-on:closeModal="closeAddClaimSourceModal"
            v-on:updateDetails="updatePatientDetails"
            shouldEmit
          />
        </div>

        <div class="modal-footer flex-end">
          <button
            :data-kt-indicator="loading ? 'on' : null"
            class="btn btn-lg btn-primary my-6"
            type="submit"
          >
            <span v-if="!loading" class="indicator-label">
              {{ buttonText }}
            </span>
            <span v-if="loading" class="indicator-progress">
              Please wait...
              <span
                class="spinner-border spinner-border-sm align-middle ms-2"
              ></span>
            </span>
          </button>
        </div>
      </div>
    </el-form>
  </div>
</template>
<script lang="ts">
import IAppointment from "@/store/interfaces/IAppointment";
import { PropType, ref, defineComponent, computed, watch } from "vue";
import store from "@/store";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import chargeTypes, { getProcedurePrice } from "@/core/data/charge-types";
import AddClaimSourceModal from "@/views/patients/modals/AddClaimSourceModal.vue";
import { convertToCurrency } from "@/core/data/billing";
import patientBillingTypes from "@/core/data/patient-billing-types";
import IPatient from "@/store/interfaces/IPatient";
import { Modal } from "bootstrap";
import { PatientActions } from "@/store/enums/StorePatientEnums";

export default defineComponent({
  name: "patient-billing-form",
  components: {
    AddClaimSourceModal,
  },
  props: {
    onSubmitExtras: { required: false, type: Function },
    appointment: { required: true, type: Object as PropType<IAppointment> },
    patient: { required: true, type: Object as PropType<IPatient> },
    buttonText: { required: false, type: String, default: "Update" },
  },
  setup(props) {
    const formRef = ref<HTMLFormElement>();
    const loading = ref<boolean>(false);
    const formData = ref<IAppointment>(props.appointment);
    const healthFundsList = computed(() => store.getters.healthFundsList);
    const selectedPatient = computed(() => props.patient);
    const claimSourceModal = ref();
    const addClaimSourceModalId = "add_claim_source_modal_check_in";

    const rules = ref({
      charge_type: [
        {
          required: true,
          message: "Charge type can not be blank",
          trigger: "change",
        },
      ],
    });

    const showAddClaimSourceModal = () => {
      if (!claimSourceModal.value) {
        claimSourceModal.value = new Modal(
          document.getElementById(`modal_${addClaimSourceModalId}`)
        );
      }

      claimSourceModal.value.show();
    };

    const closeAddClaimSourceModal = () => {
      claimSourceModal.value.hide();
    };

    const addNewClaimSource = (source) => {
      let updateData = {
        ...source,
        patient_id: selectedPatient.value.id,
      };

      loading.value = true;
      store
        .dispatch(PatientActions.CLAIM_SOURCE.ADD, updateData)
        .finally(() => {
          loading.value = false;
        });
    };

    const deleteClaimSource = (source) => {
      store.dispatch(PatientActions.CLAIM_SOURCE.DELETE, source);
    };

    const getBillingType = (type) => {
      const foundType = patientBillingTypes.find(
        (billing) => billing.value == type
      );

      return foundType?.label ?? null;
    };

    const getHealthFund = (id) => {
      const foundFund = healthFundsList.value.find((fund) => fund.code == id);

      return foundFund?.name ?? null;
    };

    const updatePatientDetails = (details) => {
      const previousData = {
        id: null,
        patient_id: selectedPatient.value.id,
        is_delete: false,
        first_name: selectedPatient.value.first_name,
        last_name: selectedPatient.value.last_name,
      };

      let updateData = {
        ...selectedPatient.value,
      };

      for (const detailName in details) {
        updateData[detailName] = details[detailName];
      }

      updateData.also_known_as.push(previousData);

      loading.value = true;
      store.dispatch(PatientActions.UPDATE, updateData).finally(() => {
        loading.value = false;
      });
    };

    const submit = () => {
      const updateData = {
        ...formData.value,
        ...props.patient,
        appointment_confirm_method:
          selectedPatient.value.appointment_confirm_method,
        id: formData.value.id,
      };

      if (formRef.value) {
        formRef.value.validate((valid) => {
          if (valid) {
            loading.value = true;
            store
              .dispatch(AppointmentActions.APT.UPDATE, updateData)
              .then(() => {
                if (props.onSubmitExtras) {
                  props.onSubmitExtras();
                }
              })
              .finally(() => {
                loading.value = false;
              });
            if (formRef.value != undefined) {
              formRef.value.resetFields();
            }
          }
        });
      }
    };

    watch(props, () => {
      formData.value = props.appointment;
    });

    return {
      formData,
      rules,
      submit,
      formRef,
      loading,
      chargeTypes,
      getProcedurePrice,
      convertToCurrency,
      getBillingType,
      getHealthFund,
      selectedPatient,
      showAddClaimSourceModal,
      closeAddClaimSourceModal,
      addNewClaimSource,
      deleteClaimSource,
      updatePatientDetails,
      claimSourceModal,
      addClaimSourceModalId,
    };
  },
});
</script>
