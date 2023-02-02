<template>
  <SettingsButton />
  <el-form
    @submit.prevent="submit()"
    :model="formData"
    :rules="rules"
    ref="formRef"
  >
    <el-form-item prop="body">
      <ckeditor :editor="editor" id="editor" v-model="formData.text" />
    </el-form-item>

    <div class="d-flex flex-row-reverse">
      <router-link
        type="reset"
        to="/settings"
        id="kt_modal_mail_compose_cancel"
        class="btn btn-light me-3"
      >
        Cancel
      </router-link>

      <button class="btn btn-lg btn-primary me-3" type="submit">
        <span class="indicator-label"> Save </span>
      </button>
    </div>
  </el-form>
</template>
<script>
import {
  defineComponent,
  onMounted,
  ref,
  watch,
  watchEffect,
  reactive,
  computed,
} from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Swal from "sweetalert2/dist/sweetalert2.min.js";
import { useRouter } from "vue-router";
import ApiService from "@/core/services/ApiService";
import Editor from "ckeditor5-custom-build/build/ckeditor";
import store from "@/store";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import SettingsButton from "@/components/SettingsButton.vue";
export default defineComponent({
  name: "pre-admission-consent",
  components: { SettingsButton },
  setup() {
    const editor = ref(Editor);
    const editorConfig = ref();
    const router = useRouter();
    const formRef = ref(null);
    const formData = ref({
      text: "",
    });
    const consent = computed(() => store.getters.consent);

    const rules = ref({
      text: [
        {
          required: true,
          message: "Please Type Text",
          trigger: "change",
        },
      ],
    });

    watch(consent, () => {
      formData.value = {
        text: consent.value.text,
      };
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("Pre-Admission Consent", ["Settings"]);
      store.dispatch(Actions.PREADMISSION.CONSENT.VIEW);
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          let data = {
            ...formData.value,
            organization_id: consent.value.organization_id,
          };
          store
            .dispatch(Actions.PREADMISSION.CONSENT.UPDATE, data)
            .then(() => {
              Swal.fire({
                text: " Pre Admission Consent Updated!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                router.push({ name: "manager-settings" });
              });
            })
            .catch(({ response }) => {
              console.log(response.data.error);
            });
        }
      });
    };

    return {
      rules,
      formData,
      submit,
      editor,
      formRef,
    };
  },
});
</script>
