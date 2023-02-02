<template>
  <div
    class="modal fade"
    id="modal_edit_anesthetic_question"
    tabindex="-1"
    aria-hidden="true"
    ref="editAnestheticQuestionModalRef"
    data-bs-backdrop="static"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_add_customer_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">Edit Anesthetic Question</h2>
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
        <!--begin::Form-->
        <el-form
          @submit.prevent="submit()"
          :model="formData"
          :rules="rules"
          ref="formRef"
        >
          <!--begin::Modal body-->
          <div class="modal-body py-10 px-lg-17">
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
              <InputWrapper prop="question">
                <el-input
                  v-model="formData.question"
                  type="text"
                  placeholder="Question"
                />
              </InputWrapper>
            </div>
            <!--end::Scroll-->
          </div>
          <!--end::Modal body-->

          <!--begin::Modal footer-->
          <div class="modal-footer flex-center">
            <!--begin::Button-->
            <button
              type="reset"
              data-bs-dismiss="modal"
              id="kt_modal_add_customer_cancel"
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
              <span v-if="!loading" class="indicator-label"> Update </span>
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

<script>
import { defineComponent, ref, watchEffect } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions } from "@/store/enums/StoreEnums";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";

export default defineComponent({
  name: "edit-anesthetic-question-modal",
  components: { InputWrapper },
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const editAnestheticQuestionModalRef = ref(null);
    const loading = ref(false);

    const formData = ref({
      question: "",
    });

    const rules = ref({
      question: [
        {
          required: true,
          message: "Question cannot be blank",
          trigger: "change",
        },
      ],
    });

    watchEffect(() => {
      formData.value = store.getters.getAneQuestionSelected;
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(Actions.ANESTHETIST_QUES.UPDATE, formData.value)
            .then(() => {
              loading.value = false;
              store.dispatch(Actions.ANESTHETIST_QUES.LIST);
              Swal.fire({
                text: "Successfully Updated!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                hideModal(editAnestheticQuestionModalRef.value);
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

    return {
      formData,
      rules,
      formRef,
      loading,
      editAnestheticQuestionModalRef,
      submit,
    };
  },
});
</script>
