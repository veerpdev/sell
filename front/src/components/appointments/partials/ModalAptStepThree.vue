<template>
  <div data-kt-stepper-element="content">
    <div class="w-100">
      <el-form
        class="w-100"
        :model="billingInfoData"
        :rules="rules"
        ref="formRef"
        @submit.prevent=""
      >
        <div class="row">
          <InputWrapper class="col-12" label="Charge Type" prop="charge_type">
            <el-select
              class="w-100"
              v-model="billingInfoData.charge_type"
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

          <div class="mb-4">
            <InfoSection :heading="'Estimated Appointment Price'">
              {{ convertToCurrency(appointmentTypeQuote / 100) }}
            </InfoSection>
          </div>

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
              v-for="(item, index) in billingInfoData.claim_sources"
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

                <div v-if="item.billing_type !== 3" class="d-flex gap-3">
                  <label class="text-primary">
                    Reference:

                    <span class="text-black">
                      {{ item.member_ref_number ?? "N/A" }}
                    </span>
                  </label>

                  <label v-if="item.billing_type == 2" class="text-primary">
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
              :patient="patientInfoData"
              :modalId="addClaimSourceModalId"
              @addClaimSource="addNewClaimSource"
              @closeModal="closeAddClaimSourceModal"
              @updateDetails="updatePatientDetails"
              shouldEmit
            />
          </div>

          <el-divider />

          <InputWrapper
            v-if="billingInfoData.charge_type === 'pension-card'"
            class="col-6"
            label="Pension Card Number"
            prop="pension_card_number"
          >
            <el-input
              class="w-100"
              v-model="billingInfoData.pension_card_number"
            />
          </InputWrapper>

          <InputWrapper
            v-if="billingInfoData.charge_type === 'healthcare-card'"
            class="col-6"
            label="Healthcare Card Number"
            prop="healthcare_card_number"
          >
            <el-input
              class="w-100"
              v-model="billingInfoData.healthcare_card_number"
            />
          </InputWrapper>
          <InputWrapper
            v-if="
              billingInfoData.charge_type === 'healthcare-card' ||
              billingInfoData.charge_type === 'pension-card'
            "
            class="col-6"
            label="Expiry Date"
            prop="expiry_date"
          >
            <el-input
              class="w-100"
              v-model="billingInfoData.healthcare_card_number"
            />
          </InputWrapper>

          <div
            class="col-sm-6 d-flex align-items-center justify-content-center"
          >
            <!--begin::Input group-->
            <div class="fv-row">
              <!--begin::Input-->
              <el-form-item class="m-0" prop="add_other_account_holder">
                <el-checkbox
                  type="checkbox"
                  v-model="billingInfoData.add_other_account_holder"
                  label="Add other account holder"
                />
              </el-form-item>
              <!--end::Input-->
            </div>
            <!--end::Input group-->

            <!--end::Body-->
          </div>
          <el-divider />
        </div>
        <div class="d-flex justify-content-between">
          <button
            type="button"
            class="btn btn-lg btn-light-primary me-3"
            data-kt-stepper-action="previous"
            @click="previousStep"
          >
            <span class="svg-icon svg-icon-4 me-1">
              <inline-svg src="media/icons/duotune/arrows/arr063.svg" />
            </span>
            Back
          </button>
          <div>
            <button
              type="button"
              class="btn btn-lg btn-light-primary me-3"
              v-if="modalId === 'update'"
              @click="handleSave"
            >
              <span v-if="!loading" class="indicator-label"> Save </span>
              <span v-if="loading" class="indicator-label">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>
            <button
              type="button"
              class="btn btn-lg btn-primary align-self-end"
              @click="handleSubmit"
            >
              Continue
              <span class="svg-icon svg-icon-4 ms-1 me-0">
                <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
              </span>
            </button>
          </div>
        </div>
      </el-form>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType, ref, watchEffect } from "vue";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";
import { useStore } from "vuex";
import { FormRulesMap } from "element-plus/es/components/form/src/form.type";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import chargeTypes from "@/core/data/charge-types";
import AddClaimSourceModal from "@/views/patients/modals/AddClaimSourceModal.vue";
import PatientBillingTypes from "@/core/data/patient-billing-types";
import { convertToCurrency } from "@/core/data/billing";
import {
  IAptInfoData,
  IBillingInfoData,
  IPatientInfoData,
} from "@/assets/ts/components/_CreateAppointmentComponent";
import { Modal } from "bootstrap";
import InfoSection from "@/components/presets/GeneralElements/InfoSection.vue";

export default defineComponent({
  components: { AddClaimSourceModal, InputWrapper, InfoSection },

  props: {
    modalId: {
      type: String,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },

    billingDataE: {
      type: Object as PropType<IBillingInfoData>,
      required: true,
    },

    patientDataE: {
      type: Object as PropType<IPatientInfoData>,
      required: true,
    },

    aptInfoDataE: {
      type: Object as PropType<IAptInfoData>,
      required: true,
    },
  },

  emits: ["save", "process", "goBack"],

  setup(props, { emit }) {
    const store = useStore();
    const rules = ref<FormRulesMap>({
      charge_type: [
        {
          required: true,
          message: "Charge Type cannot be blank.",
          trigger: "blur",
        },
      ],
    });
    const formRef = ref<null | HTMLFormElement>(null);

    const billingInfoData = ref<IBillingInfoData>({
      charge_type: chargeTypes[0].value,
      claim_sources: [],
      procedure_price: 0,
      add_other_account_holder: false,
    });

    const addClaimSourceModalId = `add_claim_source_modal_${props.modalId}`;

    const patientInfoData = ref<IPatientInfoData>({
      first_name: "",
      last_name: "",
      date_of_birth: "",
      email: "",
      address: "",
      contact_number: "",
      appointment_confirm_method: "",
      allergies: "",
      clinical_alerts: "",
      also_known_as: [],
      is_exist: false,
      alerts: [],
      is_ok: false,
    });

    const claimSourceModal = ref<HTMLElement | any>();

    const healthFundsList = computed(() => store.getters.healthFundsList);

    const appointmentTypeQuote = computed(() => {
      const id = props.aptInfoDataE.appointment_type_id;
      if (id) {
        const aptTypeList = store.getters.getAptTypesList;
        const selectedType = aptTypeList.filter(
          (aptType) => aptType.id === id
        )[0];
        if (selectedType.name) {
          return selectedType?.default_items_quote ?? 0;
        }
      }
      return 0;
    });

    watchEffect(() => {
      if (props.patientDataE) {
        for (let key in props.patientDataE)
          patientInfoData.value[key] = props.patientDataE[key];
      }
      if (props.billingDataE) {
        for (let key in props.billingDataE)
          patientInfoData.value[key] = props.billingDataE[key];
      }
    });

    const addNewClaimSource = (event) => {
      billingInfoData.value.claim_sources.push(event);
    };

    const deleteClaimSource = (source) => {
      const index = billingInfoData.value.claim_sources.indexOf(source);

      billingInfoData.value.claim_sources.splice(index, 1);

      if (Object.prototype.hasOwnProperty.call(source, "id")) {
        store.dispatch(PatientActions.CLAIM_SOURCE.DELETE, source);
      }
    };

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

    const updatePatientDetails = (details) => {
      const previousData = {
        first_name: patientInfoData.value.first_name,
        last_name: patientInfoData.value.last_name,
      };

      for (const detailName in details) {
        patientInfoData.value[detailName] = details[detailName];
      }

      patientInfoData.value.also_known_as.push(previousData);
    };

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

    const handleSave = () => {
      emit("save", billingInfoData.value, 3);
    };

    const handleSubmit = async () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          emit("process", billingInfoData.value);
        }
      });
    };

    const previousStep = () => {
      emit("goBack");
    };

    return {
      formRef,
      rules,
      billingInfoData,
      props,
      chargeTypes,
      convertToCurrency,
      appointmentTypeQuote,
      showAddClaimSourceModal,
      getBillingType,
      getHealthFund,
      deleteClaimSource,
      patientInfoData,
      addClaimSourceModalId,
      addNewClaimSource,
      closeAddClaimSourceModal,
      updatePatientDetails,
      AddClaimSourceModal,
      InputWrapper,
      handleSave,
      handleSubmit,
      previousStep,
    };
  },
});
</script>
