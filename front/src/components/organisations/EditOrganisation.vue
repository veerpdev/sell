<template>
  <CardSection heading="Organisation Restrictions">
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
    >
      <InputWrapper label="Max Clinics" props="max_clinics">
        <el-input
          type="number"
          v-model="formData.max_clinics"
          placeholder="Max Clinics"
        />
      </InputWrapper>

      <InputWrapper label="Max Employees" props="max_employees">
        <el-input
          type="number"
          v-model="formData.max_employees"
          placeholder="Max Employee"
        />
      </InputWrapper>

      <div class="d-flex flex-row">
        <el-form-item class="px-6">
          <el-checkbox
            type="checkbox"
            v-model="formData.has_billing"
            label="Has billing"
          />
        </el-form-item>
        <el-form-item class="px-6">
          <el-checkbox
            type="checkbox"
            v-model="formData.has_coding"
            label="Has coding"
          />
        </el-form-item>
      </div>

      <div class="px-6">
        <router-link
          type="reset"
          to="/organisations"
          id="kt_modal_add_customer_cancel"
          class="btn btn-light me-3"
        >
          Cancel
        </router-link>

        <button
          :data-kt-indicator="loading ? 'on' : null"
          class="btn btn-lg btn-primary"
          type="submit"
        >
          <span v-if="!loading" class="indicator-label">
            {{ formInfo.submitButtonName }}
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
      </div>
    </el-form>
  </CardSection>
</template>

<script lang="ts">
import {
  defineComponent,
  onMounted,
  ref,
  reactive,
  computed,
  watch,
} from "vue";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { Actions } from "@/store/enums/StoreEnums";

import { mask } from "vue-the-mask";
import { validatePhone } from "@/helpers/helpers";

export default defineComponent({
  name: "edit-org",
  directives: {
    mask,
  },
  components: {},
  setup() {
    const store = useStore();
    const route = useRoute();
    const formRef = ref<null | HTMLFormElement>(null);
    const loading = ref<boolean>(false);
    const orgList = computed(() => store.getters.orgList);
    const formInfo = reactive({
      title: "Edit Organisation",
      submitAction: Actions.ORG.UPDATE,
      submitButtonName: "UPDATE",
    });
    const formData = ref({
      first_name: "",
      last_name: "",
      username: "",
      name: "",
      email: "",
      mobile_number: "",
      logoUploaded: "media/avatars/300-1.jpg",
      max_clinics: "",
      max_employees: "",
      has_billing: false,
      has_coding: false,
    });
    const Data = new FormData();

    const rules = ref({
      name: [
        {
          required: true,
          message: "Organization Name cannot be blank.",
          trigger: "change",
        },
      ],
      first_name: [
        {
          required: true,
          message: "First Name cannot be blank.",
          trigger: "change",
        },
      ],
      last_name: [
        {
          required: true,
          message: "Last Name cannot be blank.",
          trigger: "change",
        },
      ],
      username: [
        {
          required: true,
          message: "Username cannot be blank.",
          trigger: "change",
        },
      ],
      email: [
        {
          required: true,
          message: "Email cannot be blank.",
          trigger: "change",
        },
        {
          type: "email",
          message: "Please input correct email address",
          trigger: ["blur", "change"],
        },
      ],
      mobile_number: [
        {
          required: true,
          message: "Mobile Number cannot be blank.",
          trigger: "change",
        },
        { validator: validatePhone, trigger: "blur" },
      ],
    });

    watch(orgList, () => {
      const id = route.params.id;
      orgList.value.forEach((item) => {
        if (item.id == id) {
          formData.value = {
            ...item,
            has_billing: item.has_billing === 1 ? true : false,
            has_coding: item.has_coding === 1 ? true : false,
          };

          formData.value.logoUploaded = item.logo;
        }
      });
    });

    const removeImage = () => {
      formData.value.logoUploaded = "media/avatars/blank.png";
      Data.delete("logo");
    };

    onMounted(() => {
      store.dispatch(Actions.ORG.LIST);

      setCurrentPageBreadcrumbs(formInfo.title, ["Organisation"]);
    });

    const changeLogo = (e) => {
      formData.value.logoUploaded = e.target.value;
      Data.append("logo", e.target.files[0]);
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;

          Object.keys(formData.value).forEach((key) => {
            if (key != "logo") {
              key !== "has_billing" &&
                key !== "has_coding" &&
                Data.append(key, formData.value[key]);
              (key === "has_billing" || key == "has_coding") &&
                Data.append(key, formData.value[key] ? "1" : "0");
            }
          });

          store
            .dispatch(formInfo.submitAction, {
              id: route.params.id,
              data: {
                ...formData.value,
                has_billing: formData.value.has_billing === true ? 1 : 0,
                has_coding: formData.value.has_coding === true ? 1 : 0,
              },
            })
            .then(() => {
              loading.value = false;
            });
        }
      });
    };

    return {
      formData,
      formInfo,
      rules,
      submit,
      formRef,
      loading,
      removeImage,
      changeLogo,
    };
  },
});
</script>
