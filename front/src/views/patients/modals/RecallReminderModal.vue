<template>
  <ModalWrapper
    title="Create Recall"
    modalId="patient_recall_reminder"
    modalRef="patientRecallReminderModal"
    :updateRef="updateRef"
  >
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      ref="formRef"
      :rules="rules"
    >
      <InputWrapper label="Time Frame" prop="time_frame">
        <el-select
          class="w-100"
          v-model="formData.time_frame"
          placeholder="Select Time Frame"
        >
          <el-option
            v-for="option in timeFrames"
            :key="option.value"
            :value="option.value"
            :label="option.label"
          />
        </el-select>
      </InputWrapper>
      <InputWrapper label="Appointment" prop="appointment">
        <el-select
          v-model="formData.appointment_id"
          class="w-100"
          placeholder="Select Appointment"
        >
          <el-option
            v-for="item in appointmentList"
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

      <InputWrapper required label="Reason" prop="reason">
        <el-input
          v-model="formData.reason"
          class="w-100"
          type="textarea"
          rows="3"
          placeholder="Reason for recall"
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

<script>
import { defineComponent, ref, watchEffect, computed, onMounted } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import timeFrames from "@/core/data/time-frames";
import moment from "moment";
export default defineComponent({
  name: "patient-recall-reminder-modal",
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const patientRecallReminderModal = ref(null);
    const loading = ref(false);
    const appointmentList = computed(() => store.getters.getAptList);

    const formData = ref({
      patient_id: 0,
      appointment_id: 1,
      time_frame: 1,
      reason: "",
    });
    const patientData = ref([]);
    const rules = ref({
      reason: [
        {
          required: true,
          message: "Reason cannot be blank",
          trigger: "change",
        },
      ],
    });
    onMounted(() => {
      store.dispatch(AppointmentActions.LIST, {
        patient_id: patientData.value.id,
      });
    });
    watchEffect(() => {
      patientData.value = store.getters.selectedPatient;
    });

    const updateRef = (_ref) => {
      patientRecallReminderModal.value = _ref;
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }
      formData.value.patient_id = patientData.value.id;
      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(PatientActions.RECALL.CREATE, formData.value)
            .then(() => {
              loading.value = false;
              Swal.fire({
                text: "Recall Created",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                formRef.value.resetFields();
                loading.value = false;
                hideModal(patientRecallReminderModal.value);
              });
            })
            .catch(({ response }) => {
              loading.value = false;
              console.log(response.data.error);
            });
        }
      });
    };

    watchEffect(() => {
      if (appointmentList.value) {
        appointmentList.value.forEach((item) => {
          if (
            moment(item.date).format("DD-MM-YYYY") ===
            moment(new Date()).format("DD-MM-YYYY")
          ) {
            formData.value.appointment_id = item.id;
          }
        });
      }
    });

    return {
      formData,
      formRef,
      loading,
      patientRecallReminderModal,
      timeFrames,
      submit,
      appointmentList,
      moment,
      updateRef,
      rules,
    };
  },
});
</script>
