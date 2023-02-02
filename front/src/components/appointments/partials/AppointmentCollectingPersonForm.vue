<template>
  <el-form
    @submit.prevent="submit()"
    :model="formData"
    :rules="rules"
    ref="formRef"
  >
    <InputWrapper label="Name" prop="collecting_person_name">
      <el-input
        v-model="formData.collecting_person_name"
        type="text"
        placeholder="Enter Name"
      />
    </InputWrapper>
    <InputWrapper label="Phone" prop="collecting_person_phone">
      <el-input
        type="text"
        v-mask="'0#-####-####'"
        v-model="formData.collecting_person_phone"
        placeholder="Enter Phone"
      />
    </InputWrapper>
    <InputWrapper
      label="Alternate Phone"
      prop="collecting_person_alternate_contact"
    >
      <el-input
        type="text"
        v-mask="'0#-####-####'"
        v-model="formData.collecting_person_alternate_contact"
        placeholder="Enter Alternate Phone"
      />
    </InputWrapper>
    <div class="modal-footer flex-end">
      <button
        :data-kt-indicator="loading ? 'on' : null"
        class="btn btn-lg btn-primary m-6"
        type="submit"
      >
        <span v-if="!loading" class="indicator-label"> {{ buttonText }} </span>
        <span v-if="loading" class="indicator-progress">
          Please wait...
          <span
            class="spinner-border spinner-border-sm align-middle ms-2"
          ></span>
        </span>
      </button>
    </div>
  </el-form>
</template>
<script lang="ts">
import IAppointment from "@/store/interfaces/IAppointment";
import { PropType, ref, defineComponent, watch } from "vue";
import { validatePhone } from "@/helpers/helpers";
import store from "@/store";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import { mask } from "vue-the-mask";

export default defineComponent({
  props: {
    onSubmitExtras: { required: false, type: Function },
    appointment: { required: true, type: Object as PropType<IAppointment> },
    buttonText: { required: false, type: String, default: "Update" },
  },
  directives: {
    mask,
  },
  setup(props) {
    const formRef = ref<HTMLFormElement>();
    const loading = ref<boolean>(false);

    const formData = ref(props.appointment);

    const rules = ref({
      collecting_person_name: [
        {
          required: true,
          message: "Name can not be blank",
          trigger: "change",
        },
      ],
      collecting_person_phone: [
        {
          required: true,
          message: "Phone can not be blank",
          trigger: "change",
        },
        { validator: validatePhone, trigger: "blur" },
      ],
      collecting_person_alternate_contact: [
        {
          required: true,
          message: "Alternate contact can not be blank",
          trigger: "change",
        },
      ],
    });

    const submit = () => {
      if (formRef.value) {
        formRef.value.validate((valid) => {
          if (valid) {
            loading.value = true;
            store
              .dispatch(AppointmentActions.COLLECTING_PERSON.UPDATE, {
                ...formData.value,
              })
              .then(() => {
                if (props.onSubmitExtras) {
                  props.onSubmitExtras();
                }
              })
              .finally(() => {
                loading.value = false;
              });
            if (formRef.value != undefined) {
              formRef.value.resetFields();
            }
          }
        });
      }
    };

    watch(props, () => {
      formData.value = props.appointment;
    });

    return {
      formData,
      rules,
      submit,
      formRef,
      loading,
    };
  },
});
</script>
