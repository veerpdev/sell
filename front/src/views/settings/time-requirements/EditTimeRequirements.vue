<template>
  <div
    class="modal fade"
    id="modal_edit_time_requirements"
    tabindex="-1"
    aria-hidden="true"
    ref="editTimeRequirementsModalRef"
    data-bs-backdrop="static"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_add_customer_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">Create Appointment Time Requirements</h2>
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
              <!--begin::Input group-->
              <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2">Title</label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="title">
                  <el-input v-model="formData.title" type="text" />
                </el-form-item>
                <!--end::Input-->
              </div>
              <!--end::Input group-->

              <!--begin::Input group-->
              <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2">Type</label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="type">
                  <el-select
                    v-model="formData.type"
                    class="w-100"
                    placeholder="Select Type"
                  >
                    <el-option value="before" label="Before" />
                    <el-option value="after" label="After" />
                  </el-select>
                </el-form-item>
                <!--end::Input-->
              </div>
              <!--end::Input group-->

              <!--begin::Input group-->
              <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2">Time</label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="base_time">
                  <el-time-picker v-model="formData.base_time" class="w-100" />
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
              <span v-if="!loading" class="indicator-label"> Create </span>
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

export default defineComponent({
  name: "edit-time-requirement",
  components: {},
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const editTimeRequirementsModalRef = ref(null);
    const loading = ref(false);

    const formData = ref({
      title: "",
      type: "",
      base_time: "",
    });

    const rules = ref({
      title: [
        {
          required: true,
          message: "First Name cannot be blank",
          trigger: "change",
        },
      ],
      type: [
        {
          required: true,
          message: "Last Name cannnot be blank",
          trigger: "change",
        },
      ],
      base_time: [
        {
          required: true,
          message: "Username cannot be blank",
          trigger: "change",
        },
      ],
    });

    watchEffect(() => {
      formData.value = store.getters.getAptTimeRequireSelected;
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(Actions.APT_TIME_REQUIREMENT.UPDATE, formData.value)
            .then(() => {
              loading.value = false;
              store.dispatch(Actions.APT_TIME_REQUIREMENT.LIST);
              Swal.fire({
                text: "Successfully Created!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                hideModal(editTimeRequirementsModalRef.value);
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
      submit,
      formRef,
      loading,
      editTimeRequirementsModalRef,
    };
  },
});
</script>
