<template>
  <CardSection>
    <HeadingText text="Patient also known as" />
    <div class="d-flex justify-content-end">
      <el-button
        type="primary"
        class="btn btn-primary m-3"
        @click="addNewKnowAs()"
        ><el-icon class="fs-5"><Plus /></el-icon
      ></el-button>
    </div>
    <el-form
      @submit.prevent="submit"
      :model="formAlsoKnowAsData"
      ref="formAlsoKnowAsRef"
    >
      <div
        class="row justify-content-md-left"
        v-for="(item, index) in formAlsoKnowAsData"
        :key="item.id"
      >
        <template v-if="!item.is_delete">
          <div class="action-width d-flex align-items-center">
            <el-avatar> {{ (index + 1).toString() }} </el-avatar>
          </div>
          <InputWrapper
            class="col-4"
            label="First Name"
            :prop="index + '.first_name'"
            required="true"
            :rule="knowRules.first_name"
          >
            <el-input
              type="text"
              v-model="item.first_name"
              placeholder="First Name"
            />
          </InputWrapper>
          <InputWrapper
            class="col-4"
            label="Last Name"
            :prop="index + '.last_name'"
            required="true"
            :rule="knowRules.last_name"
          >
            <el-input
              type="text"
              v-model="item.last_name"
              placeholder="Last Name"
            />
          </InputWrapper>
          <div class="action-width d-flex align-items-center">
            <button type="button" class="btn" @click="removeKnowAs(index)">
              Delete
            </button>
          </div>
        </template>
      </div>
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary w-25 m-3">Save</button>
      </div>
    </el-form>
  </CardSection>
</template>
<script lang="ts">
import store from "@/store";
import { PropType, ref, watchEffect } from "vue";
import IPatientAlsoKnownAs from "@/store/interfaces/IPatientAlsoKnownAs";
import IPatient from "@/store/interfaces/IPatient";
import { PatientActions } from "@/store/enums/StorePatientEnums";
export default {
  props: {
    patient: {
      required: true,
      type: Object as PropType<IPatient>,
    },
  },
  setup(props) {
    const formAlsoKnowAsRef = ref<null | HTMLFormElement>(null);
    const formAlsoKnowAsData = ref<IPatientAlsoKnownAs[]>(
      props.patient.also_known_as
    );

    const knowRules = ref({
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
          message: "Last Name cannot be blank",
          trigger: "change",
        },
      ],
    });

    const addNewKnowAs = () => {
      console.log();
      const temp = formAlsoKnowAsData.value;
      const newItem = {
        id: 0,
        patient_id: props.patient.id,
        first_name: "",
        last_name: "",
        is_delete: true,
      };
      temp.push(newItem);
      formAlsoKnowAsData.value = temp;
    };

    const removeKnowAs = (index) => {
      const temp = formAlsoKnowAsData.value;
      if (temp[index].id === 0) {
        temp.splice(index, 1);
      } else {
        temp[index]["is_delete"] = true;
      }
      formAlsoKnowAsData.value = temp;
    };

    const submit = () => {
      if (!formAlsoKnowAsRef.value) {
        return;
      }

      formAlsoKnowAsRef.value.validate((valid) => {
        if (valid) {
          const data = formAlsoKnowAsData.value.filter(
            (item) => item.first_name || item.last_name
          );
          store.dispatch(PatientActions.ALSO_KNOWN_AS.BULK, {
            id: (formAlsoKnowAsData.value as any).id,
            data: data,
          });
        }
      });
    };

    watchEffect(() => {
      formAlsoKnowAsData.value = store.getters.selectedPatient
        ? store.getters.selectedPatient.also_known_as
        : [];
    });

    return {
      formAlsoKnowAsRef,
      formAlsoKnowAsData,
      knowRules,
      addNewKnowAs,
      removeKnowAs,
      submit,
    };
  },
};
</script>
