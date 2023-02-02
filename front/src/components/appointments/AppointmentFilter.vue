x
<template>
  <div class="card border border-dashed border-primary">
    <div class="card-header">
      <div class="card-title">
        <span>FILTER VIEW</span>
      </div>
    </div>
    <div
      class="card-body card-scroll h-350px d-flex flex-column justify-content-between"
    >
      <div class="d-flex flex-column">
        <el-checkbox
          v-model="isShowAllSpecialist"
          label="Show All Specialists"
          size="large"
        />
        <el-select
          v-model="specialistsData"
          multiple
          filterable
          remote
          reserve-keyword
          placeholder="Please type a specialist name"
          remote-show-suffix
          :remote-method="remoteMethodSpecalist"
          :loading="loading"
          :disabled="isShowAllSpecialist"
          @change="filterSpecialists"
        >
          <el-option
            v-for="item in options"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
        <el-checkbox
          v-model="hideSpecialistNotWorking"
          label="Hide specialist if not working"
          size="large"
        />
      </div>
      <div class="d-flex flex-column">
        <el-checkbox
          v-model="isShowAllClinics"
          label="Show All Clinics"
          size="large"
        />
        <el-select
          v-model="clinicsData"
          multiple
          filterable
          remote
          reserve-keyword
          placeholder="Please type a clinc name"
          remote-show-suffix
          :remote-method="remoteMethodClinic"
          :loading="loading"
          :disabled="isShowAllClinics"
          @change="filterSpecialists"
        >
          <el-option
            v-for="item in clinicOptions"
            :value="item.id"
            :label="item.name"
            :key="item.id"
          />
        </el-select>
      </div>
      <button class="btn btn-light-primary w-100 mt-2" @click="handleReset">
        CLEAR FILTERS
      </button>
    </div>
  </div>
</template>

<script>
import {
  computed,
  defineComponent,
  onMounted,
  reactive,
  ref,
  watch,
} from "vue";
import moment from "moment";
import { useStore } from "vuex";
export default defineComponent({
  name: "booing-drawer",
  components: {},
  emits: ["selectedSpecialists", "clearData"],
  props: {
    date_search: {
      type: Object,
      required: true,
    },
  },
  setup(props, { emit }) {
    const store = useStore();
    const isShowAllSpecialist = ref(true);
    const isShowAllClinics = ref(true);
    const specialistsData = ref([]);
    const options = ref([]);
    const loading = ref(false);
    const clinicsData = ref([]);
    const hideSpecialistNotWorking = ref(true);
    const specialistsList = ref([]);
    const clinicOptions = ref([]);
    // The specialist that will be show in calender
    const visibleSpecialists = ref();

    const specialists = computed(() => {
      const spt = store.getters.getSpecialistList;
      spt.forEach(function (specialist) {
        specialist.value = specialist.id;
        specialist.label = `Dr. ${specialist.first_name} ${specialist.last_name}`;
      });
      return spt;
    });

    const clinic_list = computed(() => store.getters.clinicsList);

    watch([specialists, hideSpecialistNotWorking, props], () => {
      getFilterSpecialists();
      filterSpecialists();
    });

    watch(clinic_list, () => {
      getSelectedClinics();
    });

    watch(specialistsData, () => {
      let newArray = [];
      specialistsData.value.forEach(function (data) {
        newArray.push(data);
      });
      localStorage.setItem("selectedSpecialist", JSON.stringify(newArray));
      getFilterSpecialists();
    });

    watch(clinicsData, () => {
      let newArray = [];
      clinicsData.value.forEach(function (data) {
        if (data.value) {
          newArray.push(parseInt(data.value));
        } else {
          newArray.push(data);
        }
      });
      localStorage.setItem("selectedClinics", JSON.stringify(newArray));
    });

    watch([isShowAllSpecialist, isShowAllClinics], () => {
      filterSpecialists();
    });

    const remoteMethodSpecalist = (query) => {
      if (query) {
        loading.value = true;
        setTimeout(() => {
          loading.value = false;
          options.value = specialists.value.filter((item) => {
            return item.label.toLowerCase().includes(query.toLowerCase());
          });
        }, 200);
      } else {
        options.value = specialists.value.filter((item) => {
          return item;
        });
      }
    };
    const remoteMethodClinic = (query) => {
      if (query) {
        loading.value = true;
        setTimeout(() => {
          loading.value = false;
          clinicOptions.value = clinic_list.value.filter((item) => {
            return item.name.toLowerCase().includes(query.toLowerCase());
          });
        }, 200);
      } else {
        clinicOptions.value = clinic_list.value.filter((item) => {
          return item.name.toLowerCase();
        });
      }
    };

    const checkSpecialistSelectected = (id) => {
      let isSpecialistSelected = false;
      specialistsData.value.forEach(function (val) {
        if (val.value == id) isSpecialistSelected = true;
        if (val === parseInt(id)) isSpecialistSelected = true;
      });
      return isSpecialistSelected;
    };

    //Getting selected specialists from localstorage
    const getFilterSpecialists = () => {
      let localSpecialistCodes = null;
      if (localStorage.getItem("selectedSpecialist") !== null) {
        localSpecialistCodes = localStorage.getItem("selectedSpecialist");
        const savedSpecialists = JSON.parse(localSpecialistCodes);
        if (savedSpecialists.length > 0) {
          options.value = [];
          specialists.value.forEach(function (specialist) {
            options.value.push({
              value: specialist.id,
              label: `Dr. ${specialist.first_name} ${specialist.last_name}`,
            });
            savedSpecialists.forEach(function (e) {
              if (e == specialist.id) {
                specialist.checked = true;
                if (!checkSpecialistSelectected(e)) {
                  specialistsData.value.push(specialist.id);
                }
              }
            });
          });
        } else {
          isShowAllSpecialist.value = true;
        }
      } else {
        isShowAllSpecialist.value = true;
      }
    };

    const getBusinessHours = (data) => {
      const weekDays = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"]; // sun is 0
      let businessHours = [];
      data.forEach((slot) => {
        let daysOfWork = [];
        daysOfWork.push(weekDays.indexOf(slot.week_day));
        businessHours.push({
          startTime: slot.start_time,
          endTime: slot.end_time,
          daysOfWeek: daysOfWork,
        });
      });
      return businessHours;
    };

    const filterSpecialists = () => {
      specialistsList.value = [];

      // Add ALL organization specialists with appropriate data for Calender resource
      specialists.value.map((specialist) => {
        specialistsList.value.push({
          value: specialist.id,
          label: `Dr.${specialist.first_name} ${specialist.last_name}`,
          id: specialist.id,
          title: `Dr.${specialist.first_name} ${specialist.last_name}`,
          businessHours: getBusinessHours(specialist.hrm_work_schedule),
        });
      });

      // Hide not working specialists if required
      if (hideSpecialistNotWorking.value) {
        const result = [];
        specialists.value.map((specialist) => {
          let viewDay = moment(props.date_search.date)
            .format("ddd")
            .toUpperCase();
          let workOnDay = false;
          specialist.hrm_work_schedule.map((slot) => {
            if (slot.week_day == viewDay) {
              workOnDay = true;
            }
          });
          if (!workOnDay) {
            result.push(specialist.id);
          }
        });
        specialistsList.value = specialistsList.value.filter((specialist) => {
          if (!result.includes(specialist.id)) return specialist;
        });
      }

      // check user's selected specialist or show all specialist
      if (!isShowAllSpecialist.value) {
        specialistsList.value = specialistsList.value.filter((specialist) => {
          if (specialistsData.value.includes(specialist.id)) return specialist;
        });
      }

      // Check user's selected clinics or show all clinics
      if (!isShowAllClinics.value) {
        const result = [];
        specialists.value.map((specialist) => {
          specialist.hrm_work_schedule.map((slot) => {
            clinicsData.value.map((clinic) => {
              if (
                clinic == slot.clinic_id &&
                !result.includes(specialist.id) &&
                slot.week_day ===
                  moment(props.date_search.date).format("ddd").toUpperCase()
              ) {
                result.push(specialist.id);
              }
            });
          });
        });
        specialistsList.value = specialistsList.value.filter((specialist) => {
          if (result.includes(specialist.id)) return specialist;
        });
      }
      emit("selectedSpecialists", specialistsList.value);
      visibleSpecialists.value = specialistsList.value;
    };

    //Getting selected clinics from localstorage
    const getSelectedClinics = () => {
      let localClinicCodes = null;
      if (localStorage.getItem("selectedClinics") !== null) {
        localClinicCodes = JSON.parse(localStorage.getItem("selectedClinics"));
        if (localClinicCodes.length > 0) {
          clinicsData.value = [];
          remoteMethodClinic();
          localClinicCodes.forEach(function (code) {
            clinicsData.value.push(code);
          });
        } else {
          isShowAllClinics.value = true;
        }
      } else {
        isShowAllClinics.value = true;
      }
    };

    const handleReset = () => {
      emit("clearData");
    };

    onMounted(() => {
      getFilterSpecialists();
      filterSpecialists();
    });
    return {
      isShowAllSpecialist,
      remoteMethodSpecalist,
      specialistsData,
      loading,
      options,
      filterSpecialists,
      isShowAllClinics,
      remoteMethodClinic,
      clinicOptions,
      clinicsData,
      hideSpecialistNotWorking,
      handleReset,
    };
  },
});
</script>
