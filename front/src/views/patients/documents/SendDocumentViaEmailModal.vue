<template>
  <ModalWrapper
    title="Send Via Email"
    modalId="send_email"
    :updateRef="updateRef"
  >
    <el-form @submit.prevent="submit()" :model="formData" ref="formRef">
      <InputWrapper prop="practice_email">
        <el-select
          class="w-100"
          placeholder="Enter emails"
          v-model="formData.to_user_emails"
          filterable
          allow-create
          multiple
        >
          <el-option
            v-for="doctorAddressBook in doctorAddressBooks"
            :value="doctorAddressBook.practice_email"
            :label="
              doctorAddressBook.first_name +
              ' ' +
              doctorAddressBook.last_name +
              ' <' +
              doctorAddressBook.practice_email +
              '>'
            "
            :key="doctorAddressBook.practice_email"
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
  name: "send-email-modal",
  components: {},
  props: {
    document: { type: String, required: true },
  },
  setup(props) {
    const store = useStore();
    const formRef = ref(null);
    const sendEmailModalRef = ref(null);
    const loading = ref(false);
    const letter_template = ref("");
    const documentId = computed(() => props.document.id);
    // const patientId = computed(() => props.patientId);
    const doctorAddressBooks = computed(
      () => store.getters.getDoctorAddressBookList
    );
    // const sendableUsers = computed(() => store.getters.getUserList);

    const formData = ref({
      document_id: documentId,
      to_user_emails: [],
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
      sendEmailModalRef.value = _ref;
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }
      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(PatientActions.DOCUMENTS.SEND_VIA_EMAIL, formData.value)
            .then(() => {
              loading.value = false;
              formRef.value.resetFields();
              hideModal(sendEmailModalRef.value);
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
      sendEmailModalRef,
      letter_template,
      submit,
      updateRef,
    };
  },
});
</script>
