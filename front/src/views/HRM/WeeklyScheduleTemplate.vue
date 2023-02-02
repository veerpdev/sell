<template>
  <el-select
    class="w-100 mb-6"
    placeholder="Select Clinic"
    v-model="clinicFilter"
  >
    <template v-for="clinic in clinics" :key="clinic.value">
      <el-option :value="clinic.id" :label="clinic.name">
        {{ clinic.name }}
      </el-option>
    </template>
  </el-select>
  <div class="hrm-filter-container">
    <div class="filter-selector">
      <p>Employee Type</p>
      <el-select
        v-model="selectedEmployees"
        multiple
        placeholder="Select Employee"
        style="width: 240px"
      >
        <el-option
          v-for="type in employeeTypeList"
          :key="type.id"
          :label="type.name"
          :value="type.id"
        />
      </el-select>
    </div>

    <div class="filter-selector">
      <p>Display</p>
      <el-select
        v-model="selectedFilters"
        multiple
        placeholder="Select Filters"
        style="width: 240px"
      >
        <el-option
          v-for="item in filterOptions"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        />
      </el-select>
    </div>
  </div>
  <HRMTimeScheduleTable
    :selectedFilters="selectedFilters"
    :employeeList="employeeList"
    :clinicFilter="clinicFilter"
  />
</template>
<script lang="ts">
import {
  defineComponent,
  onMounted,
  computed,
  watch,
  ref,
  onBeforeUnmount,
} from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import HRMTimeScheduleTable from "@/components/HRM/HRMTimeScheduleTable.vue";
import { HRMActions, HRMMutations } from "@/store/enums/StoreHRMEnums";
import { IUser } from "@/store/modules/UserModule";

export default defineComponent({
  name: "hrm-weekly-schedule-template",
  components: {
    HRMTimeScheduleTable,
  },
  setup() {
    const store = useStore();

    const clinicFilter = ref();
    const selectedFilters = ref(["time"]);
    const selectedEmployees = ref([]);
    const filterOptions = ref([
      {
        value: "time",
        label: "Time",
      },
      {
        value: "clinic",
        label: "Clinic",
      },
      {
        value: "anesthetist",
        label: "Anesthetist",
      },
    ]);
    const clinics = computed(() => store.getters.clinicsList);
    const employeeList = computed(() => {
      const allEmployees = store.getters.hrmScheduleList;
      let filteredList = [] as Array<IUser>;
      if (selectedEmployees.value.length > 0) {
        allEmployees.filter((employee: IUser) => {
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
      const allEmployees = store.getters.hrmScheduleList;
      let list = [] as Array<unknown>;
      allEmployees.map((employee: IUser) => {
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

    onMounted(() => {
      setCurrentPageBreadcrumbs("Weekly Schedule Template", ["HRM"]);
      store.dispatch(Actions.CLINICS.LIST);
      store.dispatch(Actions.EMPLOYEE.LIST);
      store.dispatch(HRMActions.SCHEDULE_TEMPLATE.LIST);
    });

    onBeforeUnmount(() => {
      store.commit(HRMMutations.SCHEDULE.SET_LIST, []);
      store.commit(HRMMutations.SCHEDULE.SET_SELECT, []);
    });

    watch(clinics, () => {
      if (clinics.value.length) {
        clinicFilter.value = clinics.value[0].id;
      }
    });

    return {
      clinics,
      clinicFilter,
      employeeList,
      filterOptions,
      selectedFilters,
      selectedEmployees,
      employeeTypeList,
    };
  },
});
</script>
