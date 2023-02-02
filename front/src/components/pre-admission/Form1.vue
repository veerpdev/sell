<template>
  <div class="card justify-content-center h-100">
    <el-form
      class="m-auto col-sx-12 col-md-6 col-xl-4"
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
    >
      <img
        :src="orgData.organization_logo"
        alt="Organization Logo"
        class="mb-10 px-6 mx-auto d-block text-center"
      />

      <InputWrapper label="Date of Birth" prop="date_of_birth" required>
        <el-date-picker
          type="date"
          class="w-100"
          v-model="formData.date_of_birth"
          format="DD/MM/YYYY"
          placeholder="01/01/1990"
        />
      </InputWrapper>

      <InputWrapper label="Last Name" prop="last_name" required>
        <el-input
          type="text"
          v-model="formData.last_name"
          placeholder="Last Name"
        />
      </InputWrapper>

      <div class="d-flex justify-content-end gap-3">
        <button type="submit" class="btn btn-primary w-100 m-6">
          Continue
        </button>
      </div>
    </el-form>
  </div>
</template>

<style>
img {
  max-height: 150px;
}
</style>

<script>
import { defineComponent, onMounted, ref, computed } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import InputWrapper from "@/components/presets/FormElements/InputWrapper.vue";

export default defineComponent({
  name: "pre-admission-form1",
  components: { InputWrapper },
  setup() {
    const store = useStore();
    const router = useRouter();
    const loading = ref(true);
    const formRef = ref(null);
    const orgData = computed(() => store.getters.getAptPreAdmissionOrg);

    const formData = ref({
      date_of_birth: "",
      last_name: "",
    });

    const rules = ref({
      date_of_birth: [
        {
          required: true,
          message: "Date of Birth cannot be blank",
          trigger: "change",
        },
      ],
      last_name: [
        {
          required: true,
          message: "Last Name cannot be blank",
          trigger: "change",
        },
      ],
    });
    const apt_id = ref("");

    const submit = async () => {
      if (!formRef.value) {
        return;
      }
      formRef.value.validate((valid) => {
        if (valid) {
          store
            .dispatch(AppointmentActions.PRE_ADMISSION.VALIDATE, {
              id: apt_id.value,
              last_name: formData.value.last_name,
              date_of_birth: formData.value.date_of_birth,
            })
            .catch(({ response }) => {
              formData.value.last_name = "";
              formData.value.date_of_birth = "";
            });
        }
      });
    };

    onMounted(() => {
      loading.value = true;
      apt_id.value = router.currentRoute.value.params.id.toString();
      store.dispatch(
        AppointmentActions.PRE_ADMISSION.ORGANIZATION,
        apt_id.value
      );
    });

    return {
      orgData,
      formRef,
      formData,
      rules,
      submit,
    };
  },
});
</script>
