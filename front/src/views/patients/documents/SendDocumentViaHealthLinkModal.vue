<template>
  <ModalWrapper
    title="Send Via Health Link"
    modalId="send_health_link"
    :updateRef="updateRef"
  >
    <el-form @submit.prevent="submit()" :model="formData" ref="formRef">
      <InputWrapper prop="email">
        <el-select
          class="w-100"
          placeholder="Search doctor address book"
          v-model="formData.receiving_doctor_id"
          filterable
        >
          <el-option
            v-for="doctorAddressBook in doctorAddressBooks"
            :value="doctorAddressBook.id"
            :label="
              doctorAddressBook.first_name +
              ' ' +
              doctorAddressBook.last_name +
              ' <' +
              doctorAddressBook.practice_email +
              '>'
            "
            :key="doctorAddressBook.id"
          />
        </el-select>
      </InputWrapper>

      <button
        :data-kt-indicator="loading ? 'on' : null"
        class="btn btn-lg btn-primary"
        type="submit"
      >
        <span v-if="!loading" class="indicator-label"> Send </span>
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
import { defineComponent, ref, computed, onMounted } from "vue";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";

export default defineComponent({
  name: "send-health-link-modal",
  components: {},
  props: {
    document: { type: String, required: true },
  },
  setup(props) {
    const store = useStore();
    const formRef = ref(null);
    const sendHealthLinkModalRef = ref(null);
    const loading = ref(false);
    const letter_template = ref("");
    const documentId = computed(() => props.document.id);
    // const patientId = computed(() => props.patientId);
    const doctorAddressBooks = computed(
      () => store.getters.getDoctorAddressBookList
    );
    // const sendableUsers = computed(() => store.getters.getUserList);

    const formData = ref({
      patient_document_id: documentId,
      receiving_doctor_id: null,
    });

    const rules = ref({
      letter_template: [
        {
          required: true,
          message: "This field cannot be blank",
          trigger: "change",
        },
      ],
      doctor_address_book: [
        {
          required: true,
          message: "This field cannot be blank",
          trigger: "blur",
        },
      ],
      title: [
        {
          required: true,
          message: "This field cannot be blank",
          trigger: "change",
        },
      ],
    });

    const updateRef = (_ref) => {
      sendHealthLinkModalRef.value = _ref;
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }
      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(
              PatientActions.DOCUMENTS.SEND_VIA_HEALTH_LINK,
              formData.value
            )
            .then(() => {
              loading.value = false;
              formRef.value.resetFields();
              hideModal(sendHealthLinkModalRef.value);
              Swal.fire({
                text: "Successfully Sent!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              });
            })
            .catch(() => {
              loading.value = false;
            });
        } else {
          // this.context.commit(Mutations.PURGE_AUTH);
        }
      });
    };

    onMounted(() => {
      store.dispatch(Actions.USER_LIST);
      store.dispatch(Actions.DOCTOR_ADDRESS_BOOK.LIST);
    });

    return {
      formData,
      rules,
      formRef,
      loading,
      doctorAddressBooks,
      sendHealthLinkModalRef,
      letter_template,
      submit,
      updateRef,
    };
  },
});
</script>
