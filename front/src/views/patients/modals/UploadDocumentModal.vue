<template>
  <ModalWrapper
    title="Upload Document"
    modalId="upload_document"
    :updateRef="updateRef"
  >
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
    >
      <div class="row">
        <InputWrapper
          required
          class="col-6"
          label="Document Title"
          prop="document_name"
        >
          <el-input type="text" v-model="formData.document_name" />
        </InputWrapper>
        <InputWrapper
          required
          class="col-6"
          label="Document Type"
          prop="document_type"
        >
          <el-select
            class="w-100"
            placeholder="Select Document Type"
            v-model="formData.document_type"
          >
            <template
              v-for="docType in patientDocumentTypes"
              :key="docType.value"
            >
              <el-option :value="docType.value" :label="docType.label">
                <inline-svg class="me-5" :src="docType.icon" />
                {{ docType.label }}
              </el-option>
            </template>
          </el-select>
        </InputWrapper>
        <InputWrapper
          class="col-6"
          label="Attach document to:"
          v-if="attachmentType"
        >
          <el-radio-group
            v-if="attachmentType"
            v-model="attachmentType"
            size="large"
          >
            <el-radio-button label="Appointment" />
            <el-radio-button label="Specialist" />
          </el-radio-group>
        </InputWrapper>
        <InputWrapper class="col-3" label="&nbsp;" prop="is_read">
          <el-checkbox
            v-model="formData.is_read"
            label="Mark as read"
            size="large"
          />
        </InputWrapper>
        <InputWrapper class="col-3" label="&nbsp;" prop="is_urgent">
          <el-checkbox
            v-model="formData.is_urgent"
            label="Mark as urgent"
            size="large"
          />
        </InputWrapper>
        <InputWrapper
          required
          v-if="attachmentType.toLocaleLowerCase() === 'appointment'"
          class="col-6"
          label="Appointment"
          prop="appointment"
        >
          <el-select
            v-model="formData.appointment_id"
            class="w-100"
            placeholder="Select Appointment"
          >
            <el-option
              v-for="item in aptList"
              :label="
                moment(item.date).format('DD-MM-YYYY') +
                ' ' +
                item.appointment_type.name
              "
              :value="item.id"
              :key="item.id"
            />
          </el-select>
        </InputWrapper>
        <InputWrapper
          required
          class="col-6"
          label="Specialist"
          prop="specialist"
          v-if="attachmentType.toLocaleLowerCase() !== 'appointment'"
        >
          <el-select
            v-model="formData.specialist_id"
            class="w-100"
            placeholder="Select Specialist"
            :disabled="
              attachmentType.toLocaleLowerCase() == 'appointment' ? true : false
            "
          >
            <el-option
              v-for="item in specialistList"
              :label="item.full_name"
              :value="item.id"
              :key="item.id"
            />
          </el-select>
        </InputWrapper>
      </div>
      <InputWrapper required label="Upload File" prop="specialist">
        <el-upload
          v-if="!uploadDisabled"
          action="#"
          ref="upload"
          :class="{ disabled: uploadDisabled }"
          :limit="1"
          :file-list="fileList"
          :on-change="handleChange"
          :on-remove="handleRemove"
          :auto-upload="false"
          accept="image/jpeg || image/png || application/pdf"
        >
          <em class="fa fa-plus"></em> </el-upload
      ></InputWrapper>

      <!--begin::Modal footer-->
      <div class="modal-footer flex-reverse">
        <!--begin::Button-->
        <button
          type="reset"
          data-bs-dismiss="modal"
          id="kt_modal_upload_document_cancel"
          class="btn btn-light me-3"
        >
          Cancel
        </button>
        <!--end::Button-->

        <!--begin::Button-->
        <button
          :data-kt-indicator="loading ? 'on' : null"
          class="btn btn-lg btn-primary"
          type="submit"
        >
          <span v-if="!loading" class="indicator-label"> Upload </span>
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
  </ModalWrapper>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted, PropType } from "vue";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { hideModal } from "@/core/helpers/dom";
import patientDocumentTypes from "@/core/data/patient-document-types";
import moment from "moment";
import IPatient from "@/store/interfaces/IPatient";
import { ElMessage } from "element-plus";
import { DocumentMutations } from "@/store/enums/StoreDocumentEnums";
import { useRouter } from "vue-router";

export default defineComponent({
  name: "create-letter-template-modal",
  props: {
    patient: { type: Object as PropType<IPatient>, required: true },
  },
  setup(props) {
    const store = useStore();
    const router = useRouter();
    const formRef = ref();
    const uploadDocumentRef = ref(null);

    const specialistList = computed(() => store.getters.getSpecialistList);
    const aptList = computed(() => store.getters.getAptList);
    const uploadDisabled = ref<boolean>(false);
    const loading = ref<boolean>(false);
    const upload = ref();
    const attachmentType = ref("Appointment");
    let Data = new FormData();
    const fileList = ref([]);

    const formData = ref({
      patient_id: props.patient.id,
      specialist_id: "",
      document_type: "OTHER",
      appointment_id: "",
      document_name: "",
      is_read: true,
      is_urgent: false,
    });

    const rules = ref({
      document_name: [
        {
          required: true,
          message: "Document name cannot be blank",
          trigger: "change",
        },
      ],
      document_type: [
        {
          required: true,
          message: "Document type cannot be blank",
          trigger: "change",
        },
      ],
      appointment_id: [
        {
          required: true,
          message: "Appointment cannot be blank",
          trigger: "blur",
        },
      ],
      specialist_id: [
        {
          required: true,
          message: "Specialists cannot be blank",
          trigger: "change",
        },
      ],
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          const file = Data.get("file") as File;
          if (file === null) {
            ElMessage.error("Upload file!");
            return;
          }
          if (
            file.type !== "image/jpeg" &&
            file.type !== "image/png" &&
            file.type !== "application/pdf"
          ) {
            ElMessage.error("File type should be jpg, png or pdf!");
            return;
          }
          if (
            attachmentType.value.toLocaleLowerCase() === "appointment" &&
            formData.value.appointment_id === ""
          ) {
            ElMessage.error("Appointment field cannot be blank.");
            return;
          }
          if (
            attachmentType.value.toLocaleLowerCase() === "specialist" &&
            formData.value.specialist_id === ""
          ) {
            ElMessage.error("Specialist field cannot be blank.");
            return;
          }
          loading.value = true;
          Data.append("patient_id", formData.value.patient_id + "");
          Data.append("document_name", formData.value.document_name);
          Data.append("document_type", formData.value.document_type);
          Data.append("is_read", formData.value.is_read ? "1" : "0");
          Data.append("is_urgent", formData.value.is_urgent ? "1" : "0");
          if (formData.value.appointment_id !== "")
            Data.append("appointment_id", formData.value.appointment_id);
          if (formData.value.specialist_id !== "")
            Data.append("specialist_id", formData.value.specialist_id);
          store
            .dispatch(PatientActions.DOCUMENTS.CREATE, Data)
            .then((data) => {
              loading.value = false;
              hideModal(uploadDocumentRef.value);

              store.commit(DocumentMutations.SET_SELECTED_DOCUMENT, {
                id: data.id,
              });
              router.push({
                path: "/patients/" + formData.value.patient_id + "/documents",
              });
            })
            .catch(({ response }) => {
              loading.value = false;
              console.log(response.data.error);
            });
          formRef.value.resetFields();
          upload.value.clearFiles();
          fileList.value = [];
        }
      });
    };

    const updateRef = (_ref) => {
      uploadDocumentRef.value = _ref;
    };

    onMounted(() => {
      store.dispatch(Actions.SPECIALIST.LIST);
    });

    const handleChange = (file) => {
      Data = new FormData();
      upload.value.clearFiles();
      uploadDisabled.value = false;
      Data.append("file", file.raw);
    };

    const handleRemove = () => {
      fileList.value = [];
      Data.delete("file");
    };

    return {
      formData,
      rules,
      upload,
      formRef,
      loading,
      updateRef,
      specialistList,
      aptList,
      moment,
      submit,
      patientDocumentTypes,
      handleChange,
      handleRemove,
      uploadDisabled,
      fileList,
      attachmentType,
    };
  },
});
</script>
