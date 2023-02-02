<template>
  <!--begin::Navbar-->
  <div class="card pb-9">
    <ProfileNavigation />
    <div class="card-body pt-9 pb-0">
      <!--begin::Details-->
      <div class="row">
        <div
          :class="{ 'col-sm-6': currentUser.role === 'organizationManager' }"
        >
          <el-form
            @submit.prevent="submit()"
            :model="formData"
            :rules="rules"
            ref="formRef"
          >
            <div class="fv-row mb-7">
              <el-form-item>
                <label class="text-muted fs-6 fw-bold mb-2 d-block"
                  >Old Password</label
                >
                <el-form-item prop="old_password">
                  <el-input
                    type="password"
                    class="w-100"
                    v-model="formData.old_password"
                    placeholder="Old Password"
                  />
                </el-form-item>
              </el-form-item>
            </div>

            <div class="fv-row mb-7" data-kt-password-meter="true">
              <label class="text-muted fs-6 fw-bold mb-2 d-block"
                >New Password</label
              >
              <el-form-item prop="new_password">
                <el-input
                  type="password"
                  class="w-100"
                  v-model="formData.new_password"
                  placeholder="New Password"
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
            </div>

            <div class="fv-row mb-7">
              <label class="text-muted fs-6 fw-bold mb-2 d-block"
                >New Password Confirmation</label
              >
              <el-form-item prop="confirm_password">
                <el-input
                  type="password"
                  class="w-100"
                  v-model="formData.confirm_password"
                  placeholder="New Password Confirm"
                />
              </el-form-item>
            </div>

            <div class="d-flex ms-auto justify-content-end">
              <button
                type="submit"
                class="btn btn-primary"
                :data-kt-indicator="loading ? 'on' : null"
                :disabled="loading"
              >
                <span v-if="!loading" class="indicator-label">
                  Update Password
                </span>
                <span v-if="loading" class="indicator-progress">
                  Updating...
                  <span
                    class="spinner-border spinner-border-sm align-middle ms-2"
                  ></span>
                </span>
              </button>
              <button type="reset" class="btn btn-light-primary ms-2">
                Cancel
              </button>
            </div>
          </el-form>
        </div>

        <div v-if="currentUser.role === 'organizationManager'" class="col-sm-6">
          <el-form
            @submit.prevent="updatePin()"
            :model="pinFormData"
            :rules="pinRules"
            ref="pinFormRef"
          >
            <div class="fv-row mb-7">
              <el-form-item>
                <label class="text-muted fs-6 fw-bold mb-2 d-block">
                  New Pin
                </label>

                <el-form-item prop="pin">
                  <el-input
                    type="password"
                    class="w-100"
                    v-model="pinFormData.pin"
                    placeholder="New Pin"
                  />
                </el-form-item>
              </el-form-item>
            </div>

            <div
              v-if="oldPin.length > 0"
              class="d-flex gap-4 align-items-center"
            >
              <button
                class="btn btn-md btn-primary"
                type="button"
                @mousedown.prevent="showPin = true"
                @mouseup.prevent="showPin = false"
              >
                Show Current Pin
              </button>

              <p v-if="showPin" class="p-0 m-0 text-primary fw-bold">
                {{ oldPin }}
              </p>
            </div>

            <div class="d-flex ms-auto justify-content-end">
              <button
                type="submit"
                class="btn btn-primary"
                :data-kt-indicator="pinLoading ? 'on' : null"
                :disabled="pinFormData.pin.length < 4 || pinLoading"
              >
                <span v-if="!pinLoading" class="indicator-label">
                  Update Pin
                </span>
                <span v-if="pinLoading" class="indicator-progress">
                  Updating...
                  <span
                    class="spinner-border spinner-border-sm align-middle ms-2"
                  ></span>
                </span>
              </button>
              <button type="reset" class="btn btn-light-primary ms-2">
                Cancel
              </button>
            </div>
          </el-form>
        </div>
      </div>
      <!--end::Details-->
    </div>
  </div>
  <!--end::Navbar-->
</template>

<script>
import { defineComponent, ref, onMounted, computed } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import Swal from "sweetalert2/dist/sweetalert2.js";
import ProfileNavigation from "@/components/auth/ProfileNavigation";
import { PasswordMeterComponent } from "@/assets/ts/components/_PasswordMeterComponent";
import { validatePass } from "@/helpers/helpers";

export default defineComponent({
  name: "password-change",
  components: { ProfileNavigation },
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const pinFormRef = ref(null);
    const formData = ref({
      old_password: "",
      new_password: "",
      confirm_password: "",
    });
    const pinFormData = ref({
      pin: "",
    });
    const loading = ref(false);
    const pinLoading = ref(false);
    const oldPin = ref("");
    const showPin = ref(false);
    const currentUser = computed(() => store.getters.currentUser);

    const validatePass2 = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("Please input the password again"));
      } else if (value !== formData.value.new_password) {
        callback(new Error("Password doesn't match!"));
      } else {
        callback();
      }
    };

    const pinRules = ref({
      pin: [
        {
          required: true,
          message: "Pin cannot be blank",
          trigger: "change",
        },
        {
          min: 4,
          message: "The pin must be 4 digits long",
        },
        {
          max: 4,
          message: "The pin must be 4 digits long",
        },
      ],
    });

    const rules = ref({
      old_password: [
        {
          required: true,
          message: "Password cannot be blank",
          trigger: "change",
        },
        { min: 6, message: "The password must be at least 6 characters" },
      ],
      new_password: [
        { validator: validatePass, trigger: "blur" },
        {
          required: true,
          message: "Password cannot be blank",
          trigger: "change",
        },
        { min: 6, message: "The password must be at least 6 characters" },
      ],
      confirm_password: [
        { validator: validatePass2, trigger: "blur" },
        {
          required: true,
          message: "Confirm Password cannot be blank.",
          trigger: "change",
        },
        { min: 6, message: "The password must be at least 6 characters" },
      ],
    });

    const getUserPin = () => {
      if (currentUser.value.role !== "organizationManager") {
        return;
      }

      store.dispatch(Actions.PROFILE.PIN.SHOW).then((data) => {
        oldPin.value = data;
      });
    };

    const updatePin = () => {
      if (!pinFormRef.value) {
        return;
      }

      pinFormRef.value.validate((valid) => {
        if (valid) {
          pinLoading.value = true;
          store
            .dispatch(Actions.PROFILE.PIN.SET, pinFormData.value)
            .finally(() => {
              pinLoading.value = false;
              pinFormRef.value.resetFields();
              getUserPin();
            });
        }
      });
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(Actions.PROFILE.UPDATE_PASSWORD, formData.value)
            .then(() => {
              loading.value = false;
              store.dispatch(Actions.PROFILE.VIEW);
              Swal.fire({
                text: "Successfully Updated!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              });
            })
            .finally(() => {
              loading.value = false;
            });
        }
      });
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Password", ["Profile"]);
      PasswordMeterComponent.createInstances();
      getUserPin();
    });

    return {
      formData,
      formRef,
      rules,
      submit,
      oldPin,
      showPin,
      pinFormRef,
      pinFormData,
      pinRules,
      updatePin,
      pinLoading,
      loading,
      currentUser,
    };
  },
});
</script>
