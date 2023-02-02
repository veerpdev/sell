<template>
  <!--begin::Wrapper-->
  <div class="w-lg-500px bg-white rounded shadow-sm p-10 p-lg-15 mx-auto">
    <!--begin::Heading-->
    <div class="text-center mb-10">
      <img alt="Logo" src="aurora-logo.png" class="h-75px logo" />
    </div>
    <!--begin::Heading-->
    <div v-if="errorMessage" class="text-danger fw-bold fs-3 mb-6">
      {{ errorMessage }}
    </div>

    <el-form
      @submit.prevent="submit"
      :model="formData"
      :rules="rules"
      id="sign-in-form"
    >
      <div class="d-flex flex-column gap-4">
        <div v-if="!show2fa">
          <label class="fs-6 fw-bolder mb-2">Username</label>

          <el-form-item prop="username">
            <el-input type="text" v-model="formData.username" />
          </el-form-item>
        </div>

        <div v-if="!show2fa">
          <label class="fs-6 fw-bolder mb-2">Password</label>

          <el-form-item prop="password">
            <el-input type="password" v-model="formData.password" />
          </el-form-item>
        </div>

        <div v-if="show2fa">
          <p class="fs-6 text-center mb-6">
            A six-digit authentication code has been sent to you. Enter the code
            below to continue.
          </p>

          <label class="fs-6 fw-bolder mb-2">Authentication Code</label>

          <div class="d-flex flex-row gap-1 justify-content-between">
            <el-form-item prop="twoFactorCode">
              <el-input
                type="text"
                v-model="twoFactorFormData.one"
                class="two-factor"
                v-mask="'#'"
                ref="twoFactorOneRef"
                @keyup="nextFocus(1, $event)"
              />
            </el-form-item>

            <el-form-item prop="twoFactorCode">
              <el-input
                type="text"
                v-model="twoFactorFormData.two"
                class="two-factor"
                v-mask="'#'"
                ref="twoFactorTwoRef"
                @keyup="nextFocus(2, $event)"
              />
            </el-form-item>

            <el-form-item prop="twoFactorCode">
              <el-input
                type="text"
                v-model="twoFactorFormData.three"
                class="two-factor"
                v-mask="'#'"
                ref="twoFactorThreeRef"
                @keyup="nextFocus(3, $event)"
              />
            </el-form-item>

            <el-form-item prop="twoFactorCode">
              <el-input
                type="text"
                v-model="twoFactorFormData.four"
                class="two-factor"
                v-mask="'#'"
                ref="twoFactorFourRef"
                @keyup="nextFocus(4, $event)"
              />
            </el-form-item>

            <el-form-item prop="twoFactorCode">
              <el-input
                type="text"
                v-model="twoFactorFormData.five"
                class="two-factor"
                v-mask="'#'"
                ref="twoFactorFiveRef"
                @keyup="nextFocus(5, $event)"
              />
            </el-form-item>

            <el-form-item prop="twoFactorCode">
              <el-input
                type="text"
                v-model="twoFactorFormData.six"
                class="two-factor"
                v-mask="'#'"
                ref="twoFactorSixRef"
                @keyup="nextFocus(6, $event)"
              />
            </el-form-item>
          </div>

          <IconButton
            label="Resend Code"
            :disabled="stopResend"
            @click="resendTwoFactorCode"
          />
          <span v-if="countdown > 0">in {{ countdown }} second(s)</span>
        </div>

        <button
          type="submit"
          ref="submitButton"
          id="kt_sign_in_submit"
          class="btn btn-lg btn-primary w-100 mb-5"
          :disabled="loading"
          :data-kt-indicator="loading ? 'on' : 'off'"
        >
          <span class="indicator-label"> Log In </span>

          <span class="indicator-progress">
            Please wait...
            <span
              class="spinner-border spinner-border-sm align-middle ms-2"
            ></span>
          </span>
        </button>
      </div>
    </el-form>
  </div>
  <!--end::Wrapper-->
</template>

<style lang="scss">
#sign-in-form {
  .el-input__inner {
    background-color: #f5f8fa;
    border-color: #f5f8fa;
    color: #5e6278;
    border-radius: 0.625rem;
    font-size: 1.15rem;
    padding: 0.825rem 1.5rem;
    transition: color 0.2s ease, background-color 0.2s ease;
    min-height: calc(1.5em + 1.65rem + 2px);
    font-weight: 500;
    line-height: 1.5;
  }

  .el-input__inner:hover {
    border-color: #f5f8fa;
  }

  .el-input__inner:active,
  .el-input__inner:focus {
    background-color: #eef3f7;
    border-color: #eef3f7;
    outline: none;
  }

  .two-factor > .el-input__inner {
    font-size: 24px;
    text-align: center;
  }
}
</style>

<script lang="ts">
import { defineComponent, ref, computed } from "vue";
import { Actions } from "@/store/enums/StoreEnums";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import moment from "moment";
import { mask } from "vue-the-mask";
import IUserAuth from "@/store/interfaces/IUserAuth";

export default defineComponent({
  name: "sign-in",
  directives: {
    mask,
  },
  setup() {
    const store = useStore();
    const router = useRouter();
    const userRole = computed(() => store.getters.userRole);
    const user = computed(() => store.getters.currentUser);
    const organization = computed(() => store.getters.userOrganization);
    const errorMessage = ref<string>("");
    const show2fa = ref<boolean>(false);
    const loading = ref<boolean>(false);
    const twoFactorOneRef = ref<HTMLElement>();
    const twoFactorTwoRef = ref<HTMLElement>();
    const twoFactorThreeRef = ref<HTMLElement>();
    const twoFactorFourRef = ref<HTMLElement>();
    const twoFactorFiveRef = ref<HTMLElement>();
    const twoFactorSixRef = ref<HTMLElement>();
    const stopResend = ref<boolean>(false);
    const interval = ref<number>();
    const countdownTimer = ref<number>();
    const countdown = ref<number>(0);

    const formData = ref({
      username: null,
      password: null,
      twoFactorCode: null,
    });

    const twoFactorFormData = ref({
      one: null,
      two: null,
      three: null,
      four: null,
      five: null,
      six: null,
    });

    const rules = ref({
      username: [
        {
          required: true,
          message: "Username cannot be blank",
          trigger: "change",
        },
      ],
      password: [
        {
          required: true,
          message: "Password cannot be blank",
          trigger: "change",
        },
      ],
    });

    const nextFocus = (index, e) => {
      let nextStep = index;
      if (
        (e.keyCode >= 48 && e.keyCode <= 57) ||
        (e.keyCode >= 96 && e.keyCode <= 105)
      ) {
        // If it's a number
        nextStep++;
      }

      if (e.keyCode === 8) {
        // If it's backspace
        nextStep--;
      }

      switch (nextStep) {
        case 1:
          twoFactorOneRef.value?.focus();
          break;
        case 2:
          twoFactorTwoRef.value?.focus();
          break;
        case 3:
          twoFactorThreeRef.value?.focus();
          break;
        case 4:
          twoFactorFourRef.value?.focus();
          break;
        case 5:
          twoFactorFiveRef.value?.focus();
          break;
        case 6:
          twoFactorSixRef.value?.focus();
          break;
      }
    };

    const resendTwoFactorCode = () => {
      store.dispatch(Actions.RESEND_TWO_FACTOR_CODE, formData.value);
      setStopResendTimer();
    };

    const setStopResendTimer = () => {
      stopResend.value = true;
      setCountdownTimer();
      interval.value = setInterval(() => {
        stopResend.value = false;
        clearInterval(interval.value);
      }, 30000);
    };

    const setCountdownTimer = () => {
      countdown.value = 30;
      countdownTimer.value = setInterval(() => {
        countdown.value--;

        if (countdown.value === 0) {
          clearInterval(countdownTimer.value);
        }
      }, 1000);
    };

    //Form submit function
    const submit = async () => {
      // Clear existing errors
      await store.dispatch(Actions.LOGOUT);

      const user = store.getters.currentUser;

      loading.value = true;

      const submitData = {
        username: formData.value.username,
        password: formData.value.password,
      } as Record<string, unknown>;

      if (show2fa.value) {
        let code = "";

        for (const index in twoFactorFormData.value) {
          code += twoFactorFormData.value[index];
        }

        submitData.otac_code = code;
      }

      // Send login request
      await store
        .dispatch(Actions.LOGIN, submitData)
        .then((data) => {
          if (Object.prototype.hasOwnProperty.call(data, "2fa_required")) {
            errorMessage.value = "";
            show2fa.value = true;
            setStopResendTimer();
          } else {
            if (userRole.value === "organizationAdmin") {
              router.push({ name: "manager-settings" });
            }

            if (!user.outside_hours && !outsideHoursAccess()) {
              router.push({ name: "my-schedule" });
              return;
            }

            switch (userRole.value) {
              case "organizationManager":
              case "receptionist":
                router.push({ name: "booking-dashboard" });
                break;
              case "anesthetist":
                router.push({ name: "anesthetist-dashboard" });
                break;
              case "admin":
                router.push({ name: "organisations" });
                break;
              case "specialist":
                router.push({ name: "employee-booking-dashboard" });
                break;
            }
          }
        })
        .catch(() => {
          const error = store.getters.getErrors["status"];

          if (error == "failed") {
            errorMessage.value = show2fa.value
              ? "Invalid code provided. Please try again."
              : "Incorrect username or password.";
          }
        })
        .finally(() => {
          loading.value = false;
        });
    };

    const outsideHoursAccess = () => {
      if (
        user.value.role !== "admin" &&
        user.value.role !== "organizationManager"
      ) {
        const timeNow = moment();
        const orgStartTime = moment(organization.value.start_time, "HH:mm:ss");
        const orgEndTime = moment(organization.value.end_time, "HH:mm:ss");
        if (timeNow.isAfter(orgStartTime) && timeNow.isBefore(orgEndTime))
          return true;
        return false;
      }
      return true;
    };

    return {
      submit,
      errorMessage,
      show2fa,
      formData,
      rules,
      loading,
      twoFactorFormData,
      twoFactorOneRef,
      twoFactorTwoRef,
      twoFactorThreeRef,
      twoFactorFourRef,
      twoFactorFiveRef,
      twoFactorSixRef,
      nextFocus,
      resendTwoFactorCode,
      stopResend,
      countdown,
    };
  },
});
</script>
