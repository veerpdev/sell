<template>
  <ModalWrapper
    :title="'Pre-Admission Form: ' + appointmentData?.patient_name?.full"
    modalId="view_pre_admission"
    modalRef="viewPreAdmissionModalRef"
  >
    <div class="fv-row m-6 pdf_viewer_wrapper">
      <div id="preAdmissiondivPDFViewer" class="pdf_viewer">
        No Pre-Admission uploaded
      </div>
    </div>
    <el-form :model="preAdmissionData" ref="formRef">
      <el-upload
        action="#"
        ref="uploadRef"
        :limit="2"
        :on-change="handleUploadChange"
        :on-remove="handleUploadRemove"
        v-model:file-list="preAdmissionData.file"
        :auto-upload="false"
        accept="pdf/*"
      >
        <el-button class="btn btn-sm btn-info" type="primary">
          <span class="indicator-label"> Upload New Pre Admission </span>
        </el-button>
      </el-upload>

      <el-button
        v-if="!uploadDisabled"
        class="btn btn-sm btn-info ms-3"
        type="submit"
        @click="submit"
      >
        <span class="indicator-label">Upload</span>
      </el-button>
    </el-form>
  </ModalWrapper>
</template>

<style lang="scss">
.pdf_viewer_wrapper {
  height: 600px;
  > .pdf_viewer {
    height: 100%;
  }
}
</style>

<script>
import { defineComponent, ref, watch, computed } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { Actions } from "@/store/enums/StoreEnums";
import pdf from "pdfobject";
import { mask } from "vue-the-mask";
import { ElMessage } from "element-plus";

export default defineComponent({
  name: "view-pre-admission-form-modal",
  directives: {
    mask,
  },
  components: {},
  props: {
    selectedApt: { type: Object, required: true },
  },
  setup(props) {
    const store = useStore();
    const viewPreAdmissionModalRef = ref(null);
    const loading = ref(false);
    const uploadRef = ref(null);
    const uploadDisabled = ref(true);
    const pdfType = "application/pdf";
    const uploadData = new FormData();

    const appointmentData = computed(() => props.selectedApt);
    const preAdmissionData = ref({
      id: -1,
      patient_id: -1,
      patient_name: { full: "" },
      userRole: "",
      file: [],
    });

    watch(appointmentData, () => {
      if (appointmentData.value.pre_admission?.pre_admission_file) {
        store
          .dispatch(Actions.FILE.VIEW, {
            path: appointmentData.value.pre_admission.pre_admission_file,
          })
          .then((data) => {
            const blob = new Blob([data], { type: pdfType });
            const objectUrl = URL.createObjectURL(blob);
            document.getElementById("preAdmissiondivPDFViewer").innerHTML = "";
            pdf.embed(objectUrl, "#preAdmissiondivPDFViewer", {
              pdfOpenParams: { pagemode: "none" },
            });
            let file = {
              name: appointmentData.value.pre_admission?.pre_admission_file,
              raw: data,
              size: data.size,
              status: "ready",
              uid: new Date(),
            };
            preAdmissionData.value.file = [file];
          })
          .catch(() => {
            console.log("pdf error");
          });
      } else {
        preAdmissionData.value.file = [];
        document.getElementById("preAdmissiondivPDFViewer").innerHTML =
          "No Pre-Admission uploaded";
      }
    });

    const submit = () => {
      if (
        !preAdmissionData.value.file?.length ||
        preAdmissionData.value.file[0] === null ||
        preAdmissionData.value.file[0] === undefined
      ) {
        ElMessage.error("Please upload Pre-Admission");
        return;
      }
      loading.value = true;
      uploadData.append("file", preAdmissionData.value.file[0]?.raw);
      store
        .dispatch(AppointmentActions.PROCEDURE_APPROVAL.UPLOAD, {
          appointment_id: appointmentData.value.id,
          uploadData: uploadData,
        })
        .then(() => {
          loading.value = false;
          Swal.fire({
            text: "Successfully Updated Pre-Admission",
            icon: "success",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
              confirmButton: "btn btn-primary",
            },
          }).then(() => {
            uploadDisabled.value = true;
            if (props.isEditable === "true") {
              store.dispatch(AppointmentActions.PROCEDURE_APPROVAL.LIST);
            } else {
              store.dispatch(
                PatientActions.VIEW,
                appointmentData.value.patient_id
              );
            }
            document.getElementById("preAdmissiondivPDFViewer").innerHTML =
              "No Pre-Admission uploaded";
            hideModal(viewPreAdmissionModalRef.value);
          });
        })
        .catch(({ response }) => {
          loading.value = false;
          console.log(response.data.error);
        });
    };

    const handleUploadChange = (file) => {
      if (file.raw?.type?.toString().toLowerCase() !== pdfType) {
        preAdmissionData.value.file = [];
        document.getElementById("preAdmissiondivPDFViewer").innerHTML =
          "Pre-Admission must be PDF format!";
        return;
      }
      uploadDisabled.value = false;
      preAdmissionData.value.file = [file];
      if (preAdmissionData.value.file?.length) {
        const blob = new Blob([file.raw], { type: pdfType });
        const url = window.URL.createObjectURL(blob);
        document.getElementById("preAdmissiondivPDFViewer").innerHTML = "";
        pdf.embed(url, "#preAdmissiondivPDFViewer", {
          pdfOpenParams: { pagemode: "none" },
        });
      }
    };

    const handleUploadRemove = () => {
      uploadDisabled.value = true;
      preAdmissionData.value.file = [];
      document.getElementById("preAdmissiondivPDFViewer").innerHTML =
        "No Pre-Admission uploaded";
      return;
    };

    return {
      loading,
      preAdmissionData,
      viewPreAdmissionModalRef,
      uploadRef,
      submit,
      handleUploadChange,
      handleUploadRemove,
      uploadDisabled,
      appointmentData,
    };
  },
});
</script>
