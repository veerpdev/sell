<template>
  <!--begin::Stepper-->
  <div class="mx-auto w-100">
    <!--begin::Content-->
    <div class="d-flex flex-row-fluid flex-center bg-white rounded">
      <div class="w-100 py-20 px-9">
        <HeadingText text="Auto Text Details" />
        <el-form
          class="w-100"
          :rules="rules"
          :prop="formData"
          :model="formData"
          ref="formRef"
        >
          <div class="row">
            <InputWrapper
              required
              class="col-12 col-md-6"
              label="Text"
              prop="text"
            >
              <el-input
                v-model="formData.text"
                type="text"
                placeholder="Auto text"
              />
            </InputWrapper>
          </div>
          <div class="row">
            <InputWrapper
              class="col-12 col-md-6"
              label="Suggested Code"
              prop="suggested_codes"
            >
              <el-input
                v-model="formData.suggested_codes"
                type="text"
                placeholder="Suggested code"
              />
            </InputWrapper>
            <InputWrapper class="col-12 col-md-6" label="Type" prop="type">
              <el-select
                class="w-100"
                v-model="formData.report_types"
                filterable
              >
                <el-option
                  v-for="item in reportTypes"
                  :value="item.value"
                  :label="item.label"
                  :key="item.value"
                />
              </el-select>
            </InputWrapper>
          </div>
          <el-divider />
          <div class="d-flex justify-content-end">
            <button
              :disabled="loading"
              type="button"
              class="btn btn-lg btn-primary me-3"
              @click="submit()"
            >
              <span v-if="!loading" class="indicator-label">
                {{ formInfo.submitButtonName }}
              </span>
              <span v-if="loading">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>
            <RouterLink
              type="reset"
              to="/profile/auto-texts"
              class="btn btn-light me-3"
            >
              Cancel
            </RouterLink>
          </div>
        </el-form>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import {
  defineComponent,
  onMounted,
  ref,
  computed,
  reactive,
  watch,
} from "vue";
import { useStore } from "vuex";

import { useRouter, useRoute } from "vue-router";
import Swal from "sweetalert2/dist/sweetalert2.min.js";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { Actions } from "@/store/enums/StoreEnums";
import reportTypes from "@/core/data/report-types";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";
import { mask } from "vue-the-mask";

export default defineComponent({
  name: "create-auto-text",
  components: { InputWrapper },
  directives: {
    mask,
  },
  setup() {
    const store = useStore();
    const router = useRouter();
    const route = useRoute();
    const loading = ref<boolean>(false);

    const autoTextsList = computed(() => store.getters.autoTextsList);

    const formRef = ref<null | HTMLFormElement>(null);
    const formInfo = reactive({
      isCreate: true,
      title: "Create Auto Text",
      submitAction: Actions.AUTO_TEXT.CREATE,
      submitButtonName: "Create",
      submittedText: "New Auto Text Created",
      specialist_role_id: 0,
    });

    const formData = ref({
      id: -1,
      text: "",
      suggested_codes: "",
      report_types: "",
    });

    const commonRoles = {
      text: [
        {
          required: true,
          message: "Auto Text cannot be blank.",
          trigger: "change",
        },
      ],
      suggested_codes: [
        {
          required: false,
          message: "Suggest Codes can be blank.",
          trigger: "change",
        },
      ],
      report_types: [
        {
          required: false,
          message: "Report Types can be blank.",
          trigger: "change",
        },
      ],
    };

    const rules = ref(commonRoles);

    watch(autoTextsList, () => {
      const id = route.params.id;
      if (id != undefined) {
        let autoTexts = autoTextsList.value.filter((item) => item.id == id);
        if (autoTexts && autoTexts.length) {
          let autoText = autoTexts[0];
          formData.value.id = autoText.id;
          formData.value.text = autoText.text;
          formData.value.suggested_codes = autoText.suggested_codes;
          formData.value.report_types = autoText.report_types;
        }
      }
    });

    onMounted(() => {
      let id = route.params.id;
      if (id != undefined) {
        setForUpdateAutoText();
      }
      setCurrentPageBreadcrumbs(formInfo.title, ["Auto Text Settings"]);
      store.dispatch(Actions.AUTO_TEXT.LIST);
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;

          store
            .dispatch(formInfo.submitAction, formData.value)
            .then(() => {
              loading.value = false;
              store.dispatch(Actions.AUTO_TEXT.LIST);
              Swal.fire({
                text: formInfo.submittedText,
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                router.push({ name: "auto-texts" });
              });
            })
            .catch(({ response }) => {
              loading.value = false;
              console.log(response.data.error);
            });
        }
      });
    };

    const setForUpdateAutoText = () => {
      formInfo.title = "Edit";
      formInfo.isCreate = false;
      formInfo.submitAction = Actions.AUTO_TEXT.UPDATE;
      formInfo.submitButtonName = "Update";
      formInfo.submittedText = "Auto Text Updated";
      store.dispatch(Actions.AUTO_TEXT.LIST);
    };

    return {
      formData,
      formInfo,
      rules,
      submit,
      formRef,
      loading,
      reportTypes,
    };
  },
});
</script>
