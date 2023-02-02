<template>
  <ModalWrapper
    title="Create Alert"
    modalId="patient_alert"
    :updateRef="updateRef"
  >
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      ref="formRef"
      :rules="rules"
    >
      <div class="d-flex flex-row gap-3 justify-content-center">
        <LargeIconButton
          @click="setAlertLevel('NOTICE')"
          :heading="'NOTICE'"
          :iconPath="'media/icons/duotune/abstract/abs011.svg'"
          :color="formData.alert_level === 'NOTICE' ? 'success' : 'secondary'"
          iconSize="3"
        />
        <LargeIconButton
          @click="setAlertLevel('WARNING')"
          :heading="'WARNING'"
          :iconPath="'media/icons/duotune/abstract/abs011.svg'"
          :color="formData.alert_level === 'WARNING' ? 'warning' : 'secondary'"
          iconSize="3"
        />
        <LargeIconButton
          @click="setAlertLevel('BLACKLISTED')"
          :heading="'BLACKLISTED'"
          :iconPath="'media/icons/duotune/abstract/abs011.svg'"
          :color="
            formData.alert_level === 'BLACKLISTED' ? 'danger' : 'secondary'
          "
          iconSize="3"
        />
      </div>

      <InputWrapper required label="Title" prop="title">
        <el-input
          v-model="formData.title"
          class="w-100"
          type="input"
          rows="3"
          placeholder="title for alert"
        />
      </InputWrapper>
      <InputWrapper required label="Explanation" prop="explanation">
        <el-input
          v-model="formData.explanation"
          class="w-100"
          type="textarea"
          rows="3"
          placeholder="explanation for alert"
        />
      </InputWrapper>
      <!--begin::Modal footer-->
      <div class="modal-footer flex-center">
        <!--begin::Button-->
        <button type="reset" data-bs-dismiss="modal" class="btn btn-light me-3">
          Cancel
        </button>
        <!--end::Button-->

        <!--begin::Button-->
        <button
          :data-kt-indicator="loading ? 'on' : null"
          class="btn btn-lg btn-primary"
          type="submit"
        >
          <span v-if="!loading" class="indicator-label"> Save </span>
          <span v-if="loading" class="indicator-progress">
            Please wait...
            <span
              class="spinner-border spinner-border-sm align-middle ms-2"
            ></span>
          </span>
        </button>
      </div>
    </el-form>
  </ModalWrapper>
</template>

<script lang="ts">
import { defineComponent, ref } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import timeFrames from "@/core/data/time-frames";
export default defineComponent({
  name: "patient-alert-modal",
  props: {
    patientId: { type: Number, required: true },
  },
  setup(props) {
    const store = useStore();
    const formRef = ref();
    const patientAlertModal = ref();
    const loading = ref<boolean>(false);

    const formData = ref({
      patient_id: props.patientId,
      alert_level: "NOTICE",
      title: "",
      explanation: "",
    });

    const rules = ref({
      reason: [
        {
          required: true,
          message: "Explanation cannot be blank",
          trigger: "change",
        },
      ],
    });
    const setAlertLevel = (alertLevel) => {
      formData.value.alert_level = alertLevel;
    };

    const updateRef = (_ref) => {
      patientAlertModal.value = _ref;
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }
      formData.value.patient_id = props.patientId;
      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(PatientActions.ALERT.CREATE, formData.value)
            .then(() => {
              loading.value = false;
              Swal.fire({
                text: "Alert Created",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                store
                  .dispatch(PatientActions.VIEW, formData.value.patient_id)
                  .then(() => {
                    formRef.value.resetFields();
                    loading.value = false;
                    hideModal(patientAlertModal.value);
                  });
              });
            })
            .catch(({ response }) => {
              loading.value = false;
              console.log(response.data.error);
            });
        }
      });
    };

    return {
      setAlertLevel,
      formData,
      formRef,
      loading,
      patientAlertModal,
      timeFrames,
      submit,
      updateRef,
      rules,
    };
  },
});
</script>
