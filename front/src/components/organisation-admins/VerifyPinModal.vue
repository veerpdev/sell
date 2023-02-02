<template>
  <div
    class="modal fade"
    :id="`modal_${modalId}`"
    tabindex="-1"
    aria-hidden="true"
    ref="verifyAuthorizationPinModalRef"
    :is-static="true"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-400px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_add_customer_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">{{ modalTitle }}</h2>
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

        <div class="modal-body py-10 px-lg-17">
          <p class="fs-6 text-center mb-6">
            {{ message }}
            Please enter an administrator pin to continue.
          </p>

          <label class="text-muted fs-6 fw-bold mb-2 d-block">
            Authorize Access
          </label>

          <div class="d-flex flex-row gap-1 justify-content-between">
            <el-form @submit.prevent>
              <el-form-item>
                <el-input
                  type="text"
                  v-model="formData.pin_one"
                  class="verify-pin"
                  v-mask="'#'"
                  ref="pinInputOneRef"
                  @keyup="nextFocus(1, $event)"
                />
              </el-form-item>

              <el-form-item>
                <el-input
                  type="text"
                  v-model="formData.pin_two"
                  class="verify-pin"
                  v-mask="'#'"
                  ref="pinInputTwoRef"
                  @keyup="nextFocus(2, $event)"
                />
              </el-form-item>

              <el-form-item>
                <el-input
                  type="text"
                  v-model="formData.pin_three"
                  class="verify-pin"
                  v-mask="'#'"
                  ref="pinInputThreeRef"
                  @keyup="nextFocus(3, $event)"
                />
              </el-form-item>

              <el-form-item>
                <el-input
                  type="text"
                  v-model="formData.pin_four"
                  class="verify-pin"
                  v-mask="'#'"
                  ref="pinInputFourRef"
                  @keyup="nextFocus(4, $event)"
                />
              </el-form-item>
            </el-form>
          </div>

          <div class="d-flex justify-content-end">
            <button
              class="btn btn-lg btn-primary me-2"
              :data-kt-indicator="loading ? 'on' : null"
              :disabled="!isPinValid || loading"
              @click="submit"
            >
              <span v-if="!loading" class="indicator-label">Verify</span>
              <span v-if="loading" class="indicator-progress">
                Verifying...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>

            <button
              class="btn btn-lg btn-secondary"
              data-bs-dismiss="modal"
              type="submit"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.verify-pin > .el-input__inner {
  font-size: 24px;
  text-align: center;
  padding: 25px 0;
}
</style>

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
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions } from "@/store/enums/StoreEnums";
import { mask } from "vue-the-mask";

export default defineComponent({
  name: "verify-organization-pin-modal",
  directives: {
    mask,
  },
  emits: ["verified", "closeModal"],
  props: {
    title: { type: [String, null] },
    customId: { type: [String, null] },
    customMessage: { type: [String, null] },
  },
  setup(props, { emit }) {
    const store = useStore();
    const user = computed(() => store.getters.currentUser);
    const verifyAuthorizationPinModalRef = ref(null);
    const formData = ref({
      pin_one: "",
      pin_two: "",
      pin_three: "",
      pin_four: "",
    });
    const loading = ref(false);
    const modalTitle = computed(() => props.title ?? "Verify Access");
    const modalId = computed(
      () => props.customId ?? "verify_authorization_pin"
    );
    const message = computed(() => props.customMessage);
    const pinInputOneRef = ref();
    const pinInputTwoRef = ref();
    const pinInputThreeRef = ref();
    const pinInputFourRef = ref();

    const closeModal = () => {
      emit("closeModal");
    };

    const isPinValid = () => {
      if (
        formData.value.pin_one.length != 1 ||
        formData.value.pin_two.length != 1 ||
        formData.value.pin_three.length != 1 ||
        formData.value.pin_four.length != 1
      ) {
        return false;
      }

      return true;
    };

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
          pinInputOneRef.value?.focus();
          break;
        case 2:
          pinInputTwoRef.value?.focus();
          break;
        case 3:
          pinInputThreeRef.value?.focus();
          break;
        case 4:
          pinInputFourRef.value?.focus();
          break;
      }
    };

    const resetFields = () => {
      formData.value.pin_one = "";
      formData.value.pin_two = "";
      formData.value.pin_three = "";
      formData.value.pin_four = "";
    };

    const submit = () => {
      loading.value = true;
      const pin =
        formData.value.pin_one +
        formData.value.pin_two +
        formData.value.pin_three +
        formData.value.pin_four;

      store
        .dispatch(Actions.PROFILE.PIN.VERIFY, {
          pin,
        })
        .then((data) => {
          emit("verified", data.confirming_user);
        })
        .finally(() => {
          loading.value = false;
          resetFields();
        });
    };

    onMounted(() => {
      const modal = document.getElementById("modal_verify_authorization_pin");
      modal.addEventListener("shown.bs.modal", function () {
        loading.value = false;
        resetFields();
      });
    });

    return {
      store,
      formData,
      loading,
      closeModal,
      submit,
      modalTitle,
      modalId,
      message,
      nextFocus,
      isPinValid,
      pinInputOneRef,
      pinInputTwoRef,
      pinInputThreeRef,
      pinInputFourRef,
    };
  },
});
</script>
