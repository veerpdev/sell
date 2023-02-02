<template>
  <div
    class="modal fade"
    id="modal_view_pre_admission"
    tabindex="-1"
    aria-hidden="true"
    ref="viewPreAdmissionModalRef"
    data-bs-backdrop="static"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-850px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_add_customer_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">
            Procedure Assessment: {{ preAdmissionData?.patient_name?.full }}
          </h2>
          <!--end::Modal title-->

          <!--begin::Close-->
          <div
            id="kt_modal_add_customer_close"
            data-bs-dismiss="modal"
            class="btn btn-icon btn-sm btn-active-icon-primary"
          >
            <span class="svg-icon svg-icon-1">
              <InlineSVG icon="cross" />
            </span>
          </div>
          <!--end::Close-->
        </div>
        <!--end::Modal header-->

        <el-form
          @submit.prevent="submit()"
          :model="preAdmissionData"
          :rules="rules"
          ref="formRef"
        >
          <!--begin::Modal body-->
          <div class="modal-body py-2 px-lg-5">
            <!--begin::Scroll-->
            <div
              class="scroll-y me-n7 pe-7"
              id="kt_modal_add_customer_scroll"
              data-kt-scroll="true"
              data-kt-scroll-activate="{default: false, lg: true}"
              data-kt-scroll-max-height="auto"
              data-kt-scroll-dependencies="#kt_modal_add_customer_header"
              data-kt-scroll-wrappers="#kt_modal_add_customer_scroll"
              data-kt-scroll-offset="300px"
            >
              <div class="fv-row row center-row">
                <InfoSection heading="Procedure">
                  {{ preAdmissionData?.appointment_type?.name }}
                </InfoSection>
                <InfoSection heading="Date of Birth">
                  {{ preAdmissionData?.patient?.date_of_birth }}
                </InfoSection>
                <InfoSection heading="Address">
                  {{ preAdmissionData?.patient?.address }}
                </InfoSection>
                <InfoSection heading="Contact Number">
                  {{ preAdmissionData?.patient?.contact_number }}
                </InfoSection>
                <InfoSection heading="Height">
                  {{ preAdmissionData?.patient?.height }}
                </InfoSection>
                <InfoSection heading="Weight">
                  {{ preAdmissionData?.patient?.weight }}
                </InfoSection>
              </div>

              <div class="fv-row m-6 pdf_viewer_wrapper">
                <div id="divPDFViewer" class="pdf_viewer"></div>
              </div>

              <!--begin::Input group-->
              <div v-if="isEditable === 'true'" class="fv-row">
                <el-form-item prop="notes">
                  <el-input
                    type="textarea"
                    v-model="preAdmissionData.notes"
                    placeholder="Notes"
                  />
                </el-form-item>
                <!--end::Input-->
              </div>
              <!--end::Input group-->
            </div>
            <!--end::Scroll-->
          </div>
          <!--end::Modal body-->

          <!--begin::Modal footer-->
          <div class="modal-footer flex-center">
            <!--begin::Button-->
            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-md btn-warning me-4"
              type="button"
              @click="handleProcedureApproval('NOT_ASSESSED')"
            >
              <span v-if="!loading" class="indicator-label"> UNASSESSED </span>
              <span v-if="loading" class="indicator-progress">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>

            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-md btn-primary me-4"
              type="button"
              @click="handleProcedureApproval('APPROVED')"
            >
              <span v-if="!loading" class="indicator-label"> APPROVED </span>
              <span v-if="loading" class="indicator-progress">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>

            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-md btn-danger me-4"
              type="button"
              @click="handleProcedureApproval('NOT_APPROVED')"
            >
              <span v-if="!loading" class="indicator-label">
                NOT APPROVED
              </span>
              <span v-if="loading" class="indicator-progress">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>

            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-md btn-success"
              type="button"
              @click="handleProcedureApproval('CONSULT_REQUIRED')"
            >
              <span v-if="!loading" class="indicator-label">
                REQUIRES CONSULT
              </span>
              <span v-if="loading" class="indicator-progress">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>
            <!--end::Button-->
          </div>
          <!--end::Modal footer-->
        </el-form>
        <!--end::Form-->
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.pdf_viewer_wrapper {
  height: 300px;
  > .pdf_viewer {
    height: 100%;
  }
}
</style>

<script>
import { defineComponent, watchEffect, ref, watch, computed } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions } from "@/store/enums/StoreEnums";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import pdf from "pdfobject";
import InfoSection from "@/components/presets/GeneralElements/InfoSection.vue";

export default defineComponent({
  name: "view-pre-admission-form-modal",
  components: { InfoSection },
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const viewPreAdmissionModalRef = ref(null);
    const loading = ref(false);
    const uploadRef = ref(null);
    const uploadDisabled = ref(true);
    const userProfile = computed(() => store.getters.userProfile);

    const preAdmissionData = ref({
      notes: "",
    });

    const rules = ref({
      notes: [
        {
          required: false,
          message: "Notes cannot be blank",
          trigger: "change",
        },
      ],
    });

    const selectedPatient = computed(() => store.getters.selectedPatient);

    const handleProcedureApproval = (status) => {
      if (!formRef.value) {
        return;
      }

      const updateData = {
        appointment_id: preAdmissionData.value.id,
        notes: preAdmissionData.value.notes,
        procedure_approval_status: status,
      };

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(AppointmentActions.PROCEDURE_APPROVAL.UPDATE, updateData)
            .then(() => {
              loading.value = false;
              store.dispatch(AppointmentActions.LIST, {
                anesthetist_id: userProfile.value.id,
              });
              Swal.fire({
                text: "Successfully Updated!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "OK",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                hideModal(viewPreAdmissionModalRef.value);
              });
            })
            .catch(({ response }) => {
              loading.value = false;
              console.log(response.data.error);
            });
          formRef.value.resetFields();
        } else {
          // this.context.commit(Mutations.PURGE_AUTH);
        }
      });
    };

    watchEffect(() => {
      preAdmissionData.value = store.getters.getProcedureApproval;
    });

    watch(preAdmissionData, () => {
      if (preAdmissionData.value.pre_admission.pre_admission_file) {
        store
          .dispatch(Actions.FILE.VIEW, {
            path: preAdmissionData.value.pre_admission.pre_admission_file,
          })
          .then((data) => {
            const blob = new Blob([data], { type: "application/pdf" });
            const objectUrl = URL.createObjectURL(blob);
            document.getElementById("divPDFViewer").innerHTML = "";
            pdf.embed(objectUrl, "#divPDFViewer", {
              pdfOpenParams: { pagemode: "none" },
            });
          });
      }
    });

    watch(selectedPatient, () => {
      const appointments = selectedPatient.value.appointments;
      const selectedAppointment = appointments.find(
        (element) => element.id === preAdmissionData.value.id
      );

      if (selectedAppointment !== null) {
        preAdmissionData.value = selectedAppointment;
      }
    });

    return {
      preAdmissionData,
      rules,
      formRef,
      loading,
      viewPreAdmissionModalRef,
      uploadRef,
      handleProcedureApproval,
      uploadDisabled,
    };
  },
});
</script>
