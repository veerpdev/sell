<template>
  <el-dialog
    v-model="isShow"
    title="Confirm which data would you like to fill from template"
    width="30%"
    :before-close="handleClose"
    align-center
  >
    <el-checkbox v-model="isAllClinics" label="All Clinics" size="large" />
    <el-select
      placeholder="Select Clinic"
      multiple
      v-model="clinicFilter"
      class="w-100"
      :disabled="isAllClinics"
    >
      <template v-for="clinic in clinics" :key="clinic.value">
        <el-option :value="clinic.id" :label="clinic.name">
          {{ clinic.name }}
        </el-option>
      </template>
    </el-select>
    <el-checkbox
      v-model="isAllEmployees"
      label="All Employee Type"
      class="mt-4"
      size="large"
    />
    <el-select
      v-model="selectedEmployees"
      multiple
      placeholder="Select Employee Type"
      class="w-100"
      :disabled="isAllEmployees"
    >
      <el-option
        v-for="type in employeeTypeList"
        :key="type.id"
        :label="type.name"
        :value="type.id"
      />
    </el-select>
    <el-alert
      class="mt-6"
      title="Selected data will be replaced and this operation cannot be undone."
      type="warning"
      :closable="false"
      show-icon
    />
    <template #footer>
      <span class="dialog-footer">
        <el-button type="danger" @click="submit">Confirm</el-button>
        <el-button @click="cancel">Cancel</el-button>
      </span>
    </template>
  </el-dialog>
</template>
<script>
import { computed, defineComponent, ref, watch } from "vue";
import { useStore } from "vuex";

export default defineComponent({
  name: "shift-cancel-modal",
  props: {
    dialogVisible: { type: Boolean, required: true },
  },
  emits: ["selectedData"],
  setup(props, { emit }) {
    const store = useStore();
    const isShow = ref(false);
    const isAllClinics = ref(true);
    const isAllEmployees = ref(true);
    const selectedEmployees = ref([]);
    const clinics = computed(() => store.getters.clinicsList);
    const clinicFilter = ref([]);

    watch(props, () => {
      isShow.value = props.dialogVisible;
    });

    // return employee list based on user selected employee types
    const employeeList = computed(() => {
      const allEmployees = store.getters.employeeList;
      let filteredList = [];
      if (selectedEmployees.value.length > 0) {
        allEmployees.filter((employee) => {
          selectedEmployees.value.map((role) => {
            if (employee.role_id === role) {
              filteredList.push(employee);
              return employee;
            }
          });
        });
        return filteredList;
      } else return allEmployees;
    });

    const employeeTypeList = computed(() => {
      const allEmployees = store.getters.hrmWeeklyTemplatesData;
      let list = [];
      allEmployees.map((employee) => {
        if (!list.includes(employee)) list.push(employee.role);
      });
      const uniqueArray = list.filter((value, index) => {
        const _value = JSON.stringify(value);
        return (
          index ===
          list.findIndex((obj) => {
            return JSON.stringify(obj) === _value;
          })
        );
      });
      return uniqueArray;
    });

    const fillteredClinics = computed(() => {
      const list = store.getters.clinicsList;
      let data = [];
      if (!isAllClinics.value && clinicFilter.value.length > 0) {
        list.map((clinc) => {
          if (clinicFilter.value.includes(clinc.id)) data.push(clinc);
        });
        return data;
      } else return list;
    });

    const handleClose = () => {
      isShow.value = true;
    };

    const submit = () => {
      // OVERRIDE EMPLOYEE TYPE AND CLINIC FILTER FUNCTIONALITY SINCE WE ALLOW
      // PUBLISHED SHIFTS ONLY ONE TIME PER WEEK
      emit("selectedData", {
        employees: store.getters.hrmWeeklyTemplatesData,
        clinics: store.getters.clinicsList,
      });
      isShow.value = false;
    };

    const cancel = () => {
      emit("selectedData");
      isShow.value = false;
    };

    return {
      handleClose,
      isShow,
      submit,
      cancel,
      props,
      employeeList,
      selectedEmployees,
      clinics,
      isAllClinics,
      isAllEmployees,
      employeeTypeList,
      clinicFilter,
    };
  },
});
</script>
<style scoped>
.dialog-footer button:first-child {
  margin-right: 10px;
}
</style>
