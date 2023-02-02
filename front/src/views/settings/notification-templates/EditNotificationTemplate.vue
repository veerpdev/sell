<template>
  <div
    class="modal fade"
    id="modal_edit_notification_template"
    tabindex="-1"
    aria-hidden="true"
    ref="editNtfTemplateModalRef"
    data-bs-backdrop="static"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_add_customer_header">
          <!--begin::Modal title-->
          <h2 v-if="formData" class="fw-bolder">
            Edit Notification Template: {{ formData.title }}
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
        <!--begin::Form-->
        <el-form
          @submit.prevent="submit()"
          :model="formData"
          :rules="rules"
          ref="formRef"
          v-if="formData"
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
              <!--begin::Input group-->
              <div v-if="formData.allow_day_edit == true" class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2">Days Before</label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="days_before">
                  <el-input-number v-model="formData.days_before" :min="1" />
                </el-form-item>
                <!--end::Input-->
              </div>
              <!--end::Input group-->

              <!--begin::Input group-->
              <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2">SMS Template</label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="sms_template">
                  <el-input
                    v-model="formData.sms_template"
                    type="textarea"
                    rows="3"
                    placeholder="SMS Template"
                  />
                </el-form-item>
                <!--end::Input-->
              </div>
              <!--end::Input group-->

              <!--begin::Input group-->
              <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2"
                  >Email Print Template</label
                >
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="email_print_template">
                  <el-input
                    v-model="formData.email_print_template"
                    type="textarea"
                    rows="3"
                    placeholder="Email Print Template"
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

<script lang="ts">
import { defineComponent, ref, watchEffect } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import { Actions } from "@/store/enums/StoreEnums";
import INotificationTemplate from "@/store/interfaces/INotificationTemplate";

export default defineComponent({
  name: "edit-notification-template",
  components: {},
  setup() {
    const store = useStore();
    const formRef = ref<HTMLFormElement>();
    const editNtfTemplateModalRef = ref(null);
    const loading = ref<boolean>(false);

    const formData = ref<INotificationTemplate>();

    const rules = ref({
      sms_template: [
        {
          required: true,
          message: "SMS template cannot be blank",
          trigger: "change",
        },
      ],
      email_print_template: [
        {
          required: true,
          message: "Email print template cannot be blank",
          trigger: "change",
        },
      ],
    });

    watchEffect(() => {
      formData.value = store.getters.getNotificationTemplateSelected;
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(Actions.NTF_TEMPLATES.UPDATE, formData.value)
            .then(() => {
              store.dispatch(Actions.NTF_TEMPLATES.LIST).then(() => {
                hideModal(editNtfTemplateModalRef.value);
              });
            })
            .finally(() => {
              loading.value = false;
              hideModal(editNtfTemplateModalRef.value);
            });
          if (formRef.value != undefined) {
            formRef.value.resetFields();
          }
        }
      });
    };

    return {
      formData,
      rules,
      formRef,
      loading,
      editNtfTemplateModalRef,
      submit,
    };
  },
});
</script>
