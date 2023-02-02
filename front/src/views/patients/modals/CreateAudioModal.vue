<template>
  <ModalWrapper
    title="Create Audio"
    modalId="create_audio"
    modalRef="letterModalRef"
  >
    <el-form @submit.prevent="submit()" :model="formData" ref="formRef">
      ADD AUDIO RECORDER
      <button
        :data-kt-indicator="loading ? 'on' : null"
        class="btn btn-lg btn-primary"
        type="submit"
      >
        <span v-if="!loading" class="indicator-label"> Create </span>
        <span v-if="loading" class="indicator-progress">
          Please wait...
          <span
            class="spinner-border spinner-border-sm align-middle ms-2"
          ></span>
        </span>
      </button>
    </el-form>
  </ModalWrapper>
</template>

<script>
import {
  defineComponent,
  ref,
  computed,
  onMounted,
  watchEffect,
  watch,
} from "vue";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import moment from "moment";

export default defineComponent({
  name: "create-letter-template-modal",
  props: {
    patientId: { required: true },
  },
  setup(props) {
    const store = useStore();
    const formRef = ref(null);
    const createAudioModalRef = ref(null);
    const loading = ref(false);
    const specialistList = computed(() => store.getters.getSpecialistList);
    const aptList = computed(() => store.getters.getAptListById);
    const patientId = computed(() => props.patientId);
    const uploadDisabled = ref(false);
    const upload = ref(null);
    const Data = new FormData();

    const formData = ref({
      document_title: "",
    });

    const rules = ref({
      document_type: [
        {
          required: true,
          message: "This field cannot be blank",
          trigger: "change",
        },
      ],
      appointment_id: [
        {
          required: true,
          message: "This field cannot be blank",
          trigger: "blur",
        },
      ],
      specialist_id: [
        {
          required: true,
          message: "This field cannot be blank",
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
          loading.value = true;
          Object.keys(formData.value).forEach((key) => {
            Data.append(key, formData.value[key]);
          });

          store
            .dispatch(Actions.DOCUMENT.AUDIO.CREATE, Data)
            .then(() => {
              loading.value = false;
              Swal.fire({
                text: "Successfully Created!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                hideModal(createAudioModalRef.value);
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

    watch(patientId, () => {
      formData.value.patient_id = patientId.value;
      //  store.dispatch(Actions.APT.LISTBYID, patientId.value);
    });

    watchEffect(() => {
      if (aptList.value && aptList.value.futureAppointments) {
        aptList.value.futureAppointments.forEach((item) => {
          if (
            moment(item.date).format("DD-MM-YYYY") ===
            moment(new Date()).format("DD-MM-YYYY")
          ) {
            formData.value.appointment_id = item.id;
            formData.value.specialist_id = item.specialist_id;
          }
        });
      }
    });

    onMounted(() => {
      store.dispatch(Actions.SPECIALIST.LIST);
    });

    const handleChange = (file, fileList) => {
      upload.value.clearFiles();
      uploadDisabled.value = false;
      Data.append("file", file.raw);
      uploadDisabled.value = fileList.length >= 1;
    };

    const handleRemove = (file, fileList) => {
      uploadDisabled.value = fileList.length - 1;
      Data.delete("file");
    };

    return {
      formData,
      rules,
      upload,
      formRef,
      loading,
      createAudioModalRef,
      specialistList,
      aptList,
      moment,
      submit,
      handleChange,
      handleRemove,
      uploadDisabled,
    };
  },
});
</script>
