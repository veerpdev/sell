<template>
  <div
    class="modal fade"
    id="modal_add_orgManager"
    tabindex="-1"
    aria-hidden="true"
    ref="addOrgManagerModalRef"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_add_customer_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">Create Manager</h2>
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
                <label class="required fs-6 fw-bold mb-2">First Name</label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="first_name">
                  <el-input
                    v-model="formData.first_name"
                    type="text"
                    placeholder="First Name"
                  />
                </el-form-item>
                <!--end::Input-->
              </div>
              <!--end::Input group-->

              <!--begin::Input group-->
              <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2">Last Name</label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="last_name">
                  <el-input
                    v-model="formData.last_name"
                    type="text"
                    placeholder="Last Name"
                  />
                </el-form-item>
                <!--end::Input-->
              </div>
              <!--end::Input group-->

              <!--begin::Input group-->
              <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2">Username</label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="username">
                  <el-input
                    v-model="formData.username"
                    type="text"
                    placeholder="Username"
                  />
                </el-form-item>
                <!--end::Input-->
              </div>
              <!--end::Input group-->

              <!--begin::Input group-->
              <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="fs-6 fw-bold mb-2">
                  <span class="required">Email</span>
                  <em
                    class="fas fa-exclamation-circle ms-1 fs-7"
                    data-bs-toggle="tooltip"
                    title="Email address must be active"
                  ></em>
                </label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="email">
                  <el-input v-model="formData.email" />
                </el-form-item>
                <!--end::Input-->
              </div>
              <!--end::Input group-->

              <div class="fv-row mb-7" data-kt-password-meter="true">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2">Password</label>
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="password">
                  <el-input
                    v-model="formData.password"
                    type="password"
                    placeholder="Password"
                  />
                  <span
                    class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                    data-kt-password-meter-control="visibility"
                  >
                    <em class="bi bi-eye-slash fs-2"></em>
                    <em class="bi bi-eye fs-2 d-none"></em>
                  </span>
                </el-form-item>
                <div
                  class="d-flex align-items-center mb-3"
                  data-kt-password-meter-control="highlight"
                >
                  <div
                    class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"
                  ></div>
                  <div
                    class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"
                  ></div>
                  <div
                    class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"
                  ></div>
                  <div
                    class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"
                  ></div>
                </div>
                <!--end::Input-->
              </div>

              <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fs-6 fw-bold mb-2"
                  >Confirm Password</label
                >
                <!--end::Label-->

                <!--begin::Input-->
                <el-form-item prop="password_confirmation">
                  <el-input
                    v-model="formData.password_confirmation"
                    type="password"
                    placeholder="Confirm Password"
                  />
                </el-form-item>
                <!--end::Input-->
              </div>
            </div>
            <!--end::Scroll-->
          </div>
          <!--end::Modal body-->

          <!--begin::Modal footer-->
          <div class="modal-footer flex-center">
            <!--begin::Button-->
            <button
              type="reset"
              id="kt_modal_add_customer_cancel"
              class="btn btn-light me-3"
            >
              Discard
            </button>
            <!--end::Button-->

            <!--begin::Button-->
            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-lg btn-primary"
              type="submit"
            >
              <span v-if="!loading" class="indicator-label">
                Submit
                <span class="svg-icon svg-icon-3 ms-2 me-0">
                  <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
                </span>
              </span>
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
import { defineComponent, ref, onMounted } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions } from "@/store/enums/StoreEnums";
import { PasswordMeterComponent } from "@/assets/ts/components/_PasswordMeterComponent";
import { validatePass } from "@/helpers/helpers";

export default defineComponent({
  name: "add-org-manager-modal",
  components: {},
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const addOrgManagerModalRef = ref(null);
    const loading = ref(false);
    const formData = ref({
      first_name: "",
      last_name: "",
      username: "",
      email: "",
      password: "",
      password_confirmation: "",
    });

    const validatePass2 = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("Please input the password again"));
      } else if (value !== formData.value.password) {
        callback(new Error("Password doesn't match!"));
      } else {
        callback();
      }
    };

    const rules = ref({
      first_name: [
        {
          required: true,
          message: "First Name cannot be blank",
          trigger: "change",
        },
      ],
      last_name: [
        {
          required: true,
          message: "Last Name cannnot be blank",
          trigger: "change",
        },
      ],
      username: [
        {
          required: true,
          message: "Username cannot be blank",
          trigger: "change",
        },
      ],
      email: [
        {
          required: true,
          message: "Email cannot be blank",
          trigger: "change",
        },
      ],
      password: [
        { validator: validatePass, trigger: "blur" },
        {
          required: true,
          message: "Password cannot be blank",
          trigger: "change",
        },
        { min: 6, message: "The password must be at least 6 characters" },
      ],
      password_confirmation: [
        { validator: validatePass2, trigger: "blur" },
        {
          required: true,
          message: "Confirm Password cannot be blank.",
          trigger: "change",
        },
        { min: 6, message: "The password must be at least 6 characters" },
      ],
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;

          store
            .dispatch(Actions.ORG_MANAGER.CREATE, formData.value)
            .then(() => {
              loading.value = false;
              store.dispatch(Actions.ORG_MANAGER.LIST);
              Swal.fire({
                text: "Successfully Created!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                hideModal(addOrgManagerModalRef.value);
              });
            })
            .catch(({ response }) => {
              loading.value = false;
              console.log(response.data.error);
            });
        }
      });
    };

    onMounted(() => {
      PasswordMeterComponent.createInstances();
    });

    return {
      formData,
      rules,
      submit,
      formRef,
      loading,
      addOrgManagerModalRef,
    };
  },
});
</script>
