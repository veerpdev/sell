<template>
  <el-form
    @submit.prevent="submit()"
    :model="formData"
    :rules="rules"
    ref="formRef"
  >
    <HeadingText text="Appointment Referral" />

    <div class="row justify-content-md-center mt-4">
      <el-checkbox
        v-model="formData.is_no_referral"
        label="No referral required"
        class="mb-4"
      />

      <div class="d-flex flex-column flex-md-row gap-4">
        <InputWrapper
          label="Referring Doctor"
          prop="doctor_address_book_id"
          class="flex-fill"
        >
          <el-select
            class="w-100"
            v-model="formData.doctor_address_book_id"
            placeholder="Select Referring Doctor"
          >
            <el-option
              v-for="item in doctorAddressBookList"
              :key="item.id"
              :value="item.id"
              :label="item.full_name"
            />
          </el-select>
        </InputWrapper>

        <InputWrapper
          label="Referral Date"
          prop="referral_date"
          class="flex-fill"
        >
          <el-date-picker
            editable
            class="w-100"
            format="DD-MM-YYYY"
            v-model="formData.referral_date"
          />
        </InputWrapper>
      </div>

      <div class="d-flex flex-column flex-md-row gap-4">
        <div class="d-flex align-items-center flex-fill">
          <InputWrapper
            label="Referral Duration"
            prop="referral_duration"
            class="flex-grow-1 fill-out"
          >
            <el-input
              v-model="formData.referral_duration"
              type="number"
              min="0"
              max="24"
              placeholder="Enter Referral Duration"
            />
          </InputWrapper>

          <p class="flex-grow-0 mb-0 fs-6">Months</p>
        </div>

        <el-upload
          action="#"
          ref="uploadRef"
          class="flex-fill ms-6"
          :limit="2"
          :auto-upload="false"
          :on-change="handleUploadChange"
          :on-remove="handleUploadRemove"
          v-model:file-list="files"
        >
          <el-button type="primary" class="btn btn-primary"
            >Upload Referral
          </el-button>
        </el-upload>
      </div>

      <div v-show="files.length > 0" class="fv-row m-6 pdf_viewer_wrapper">
        <div id="divPDFViewer" class="pdf_viewer"></div>
      </div>

      <div class="modal-footer flex-end gap-3">
        <button
          v-if="showCancel"
          :data-kt-indicator="loading ? 'on' : null"
          class="btn btn-lg btn-light"
          @click.prevent="$emit('cancel')"
        >
          Cancel
        </button>

        <button
          :data-kt-indicator="loading ? 'on' : null"
          class="btn btn-lg btn-primary me-6"
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
</template>
<script lang="ts">
import IAppointment from "@/store/interfaces/IAppointment";
import {
  PropType,
  ref,
  defineComponent,
  onMounted,
  computed,
  watch,
  watchEffect,
} from "vue";
import store from "@/store";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import { Actions } from "@/store/enums/StoreEnums";
import IAppointmentReferral from "@/store/interfaces/IAppointmentReferral";
import IFile from "@/store/interfaces/IFile";
import pdf from "pdfobject";
import moment from "moment";

export default defineComponent({
  props: {
    onSubmitExtras: { required: false, type: Function },
    appointment: { required: true, type: Object as PropType<IAppointment> },
    buttonText: { required: false, type: String, default: "Update" },
    showCancel: { required: false, type: Boolean, default: false },
  },
  setup(props) {
    const formRef = ref<HTMLFormElement>();
    const loading = ref<boolean>(false);
    const doctorAddressBookList = computed(
      () => store.getters.getDoctorAddressBookList
    );
    const formData = ref(
      props.appointment?.referral ?? ({} as IAppointmentReferral)
    );
    const files = ref<Array<IFile>>([]);
    const pdfType = "application/pdf";

    const rules = ref({
      is_no_referral: [
        {
          required: true,
          message: "Must select if referral is required",
          trigger: "change",
        },
      ],
    });

    const handleUploadChange = (file) => {
      const pdfViewer = document.getElementById("divPDFViewer");

      if (file.raw?.type?.toString().toLowerCase() !== pdfType) {
        files.value = [];

        if (pdfViewer) {
          pdfViewer.innerHTML = "Referral must be PDF format!";
        }

        return;
      }

      files.value = [file];
      if (files.value.length) {
        const blob = new Blob([file.raw], { type: pdfType });
        const url = window.URL.createObjectURL(blob);
        if (pdfViewer) {
          pdfViewer.innerHTML = "";
        }
        pdf.embed(url, "#divPDFViewer");
      }
    };

    const handleUploadRemove = (event) => {
      const fileIndex = files.value.findIndex(
        (file) => file.name === event.name
      );
      files.value.splice(fileIndex, 1);
    };

    const submit = () => {
      if (formRef.value) {
        formRef.value.validate((valid) => {
          if (valid) {
            let submitData = new FormData();
            if (files.value?.length && files.value.length > 0) {
              submitData.append("file", files.value[0]?.raw);
            }
            submitData.append(
              "doctor_address_book_id",
              formData.value.doctor_address_book_id.toString()
            );
            submitData.append(
              "referral_date",
              moment(formData.value.referral_date).format("YYYY-MM-DD")
            );
            submitData.append(
              "referral_duration",
              formData.value.referral_duration
            );

            const updateData = {
              appointment_id: props.appointment.id,
              submitData,
            };

            loading.value = true;
            store
              .dispatch(AppointmentActions.REFERRAL.UPDATE, updateData)
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

    watchEffect(() => {
      formData.value =
        props.appointment?.referral ?? ({} as IAppointmentReferral);
      const pdfViewer = document.getElementById("divPDFViewer");

      if (pdfViewer) {
        pdfViewer.innerHTML = "<div>Error loading PDF</div>";
      }
      if (
        props.appointment.referral?.patient_document_id &&
        props.appointment.referral.patient_document_file_path
      ) {
        store
          .dispatch(Actions.FILE.VIEW, {
            path: props.appointment.referral.patient_document_file_path,
          })
          .then((data) => {
            const blob = new Blob([data], { type: "application/pdf" });
            const objectUrl = URL.createObjectURL(blob);
            let file = {
              name: props.appointment.referral?.referral_file,
              raw: data,
              size: data.size,
              status: "ready",
              uid: new Date(),
            };

            files.value = [file];
            pdf.embed(objectUrl, "#divPDFViewer");
          });
      }
    });

    onMounted(() => {
      store.dispatch(Actions.DOCTOR_ADDRESS_BOOK.LIST);
    });

    return {
      formData,
      rules,
      submit,
      formRef,
      loading,
      doctorAddressBookList,
      files,
      handleUploadChange,
      handleUploadRemove,
    };
  },
});
</script>
