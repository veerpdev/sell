<template>
  <div
    class="modal fade"
    id="modal_employee_password"
    tabindex="-1"
    aria-hidden="true"
    ref="employeePasswordModalRef"
    data-bs-backdrop="static"
  >
    <div class="modal-dialog modal-dialog-centered mw-650px">
      <div class="modal-content">
        <div class="modal-header" id="kt_modal_provider_header">
          <h2 class="fw-bolder">Employee Password Update</h2>
          <div
            id="kt_modal_provider_close"
            data-bs-dismiss="modal"
            class="btn btn-icon btn-sm btn-active-icon-primary"
          >
            <span class="svg-icon svg-icon-1">
              <inline-svg src="media/icons/duotune/arrows/arr061.svg" />
            </span>
          </div>
        </div>
        <el-form
          @submit.prevent="submit()"
          :rules="rules"
          :model="formData"
          ref="formRef"
        >
          <div class="modal-body py-10 px-lg-17">
            <InputWrapper
              required
              label="Current your password"
              prop="current_password"
            >
              <el-input
                v-model="formData.current_password"
                type="password"
                placeholder="Enter current your password"
              />
            </InputWrapper>
            <div data-kt-password-meter="true">
              <InputWrapper
                required
                label="New employee password"
                prop="new_employee_password"
              >
                <el-input
                  v-model="formData.new_employee_password"
                  type="password"
                  placeholder="Enter new employee password"
                />
                <span
                  class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                  data-kt-password-meter-control="visibility"
                >
                  <em class="bi bi-eye-slash fs-2"></em>
                  <em class="bi bi-eye fs-2 d-none"></em>
                </span>
                <div
                  class="d-flex align-items-center mt-3"
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
              </InputWrapper>
            </div>

            <InputWrapper
              required
              label="Repeat employee password"
              prop="repeat_employee_password"
            >
              <el-input
                v-model="formData.repeat_employee_password"
                type="password"
                placeholder="Repeat employee password"
              />
            </InputWrapper>
          </div>
          <div class="modal-footer flex-center">
            <button
              type="reset"
              data-bs-dismiss="modal"
              class="btn btn-light me-3"
            >
              Cancel
            </button>
            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-lg btn-primary m-6"
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
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from "vue";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { PasswordMeterComponent } from "@/assets/ts/components/_PasswordMeterComponent";
import { validatePass } from "@/helpers/helpers";

export default defineComponent({
  name: "update-employee-password-modal",
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const employeePasswordModalRef = ref(null);
    const loading = ref(false);
    const employee = computed(() => store.getters.employeeSelected);

    const formData = ref([
      {
        current_password: null,
        new_employee_password: null,
        repeat_employee_password: null,
      },
    ]);

    const validatePass2 = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("Please input the password again"));
      } else if (value !== formData.value.new_employee_password) {
        callback(new Error("Password doesn't match!"));
      } else {
        callback();
      }
    };
    const rules = ref({
      current_password: [
        {
          required: true,
          message: "This field cannot be blank",
          trigger: ["blur", "change"],
        },
        { min: 6, message: "The password must be at least 6 characters" },
      ],
      new_employee_password: [
        { validator: validatePass, trigger: "blur" },
        {
          required: true,
          message: "This field cannot be blank",
          trigger: ["blur", "change"],
        },
        { min: 6, message: "The password must be at least 6 characters" },
      ],
      repeat_employee_password: [
        { validator: validatePass2, trigger: "blur" },
        {
          required: true,
          message: "This field cannot be blank",
          trigger: ["blur", "change"],
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
            .dispatch(Actions.EMPLOYEE.UPDATE_PASSWORD, {
              id: employee.value.id,
              old_password: formData.value.current_password,
              new_password: formData.value.new_employee_password,
              confirm_password: formData.value.repeat_employee_password,
            })
            .then((data) => {
              loading.value = false;
              if (data && data.success) {
                Swal.fire({
                  text: "Successfully Updated!",
                  icon: "success",
                  buttonsStyling: false,
                  confirmButtonText: "Ok, got it!",
                  customClass: {
                    confirmButton: "btn btn-primary",
                  },
                }).then(() => {
                  hideModal(employeePasswordModalRef.value);
                });
              } else {
                throw new Error(
                  data
                    ? data.message
                      ? data.message
                      : data.errors.old_password ||
                        data.errors.new_password ||
                        data.errors.confirm_password
                    : "Action failed."
                );
              }
            })
            .catch((e) => {
              Swal.fire({
                text: e.message ? e.message : "Action failed.",
                icon: "failed",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              });
            });
          formRef.value.resetFields();
        } else {
          // this.context.commit(Mutations.PURGE_AUTH);
        }
      });
    };

    // watch(employee, () => {});

    onMounted(() => {
      store.dispatch(Actions.CLINICS.LIST);
      PasswordMeterComponent.createInstances();
    });

    return {
      formData,
      rules,
      formRef,
      loading,
      employeePasswordModalRef,
      submit,
    };
  },
});
</script>
