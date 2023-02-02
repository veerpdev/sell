<template>
  <div class="card border border-dashed border-primary">
    <div class="card-header">
      <div class="card-title">
        <span>SEARCH AVAILABLE APPOINTMENTS</span>
      </div>
    </div>
    <div class="card-body card-scroll h-350px">
      <div class="card-info">
        <el-form
          ref="searchAppointmentFormRef"
          :model="searchAppointmentForm"
          :rules="searchAppointmentRules"
        >
          <el-form-item prop="appointment_type_id">
            <el-select
              class="w-100"
              placeholder="Select Appointment Type"
              v-model="searchAppointmentForm.appointment_type_id"
            >
              <el-option
                v-for="item in aptTypelist"
                :value="item.id"
                :label="item.name"
                :key="item.id"
              />
            </el-select>
          </el-form-item>
          <el-divider />
          <div>
            <el-select
              class="w-50 p-2"
              placeholder="Select Clinic"
              v-model="searchAppointmentForm.clinic_id"
            >
              <el-option value="" label="Any Clinic" />
              <el-option
                v-for="item in clinic_list"
                :value="item.id"
                :label="item.name"
                :key="item.id"
              />
            </el-select>
            <el-select
              class="w-50 p-2"
              placeholder="Select Specialist"
              v-model="searchAppointmentForm.specialist_id"
              filterable
            >
              <el-option value="" label="Any Specialist" />
              <el-option
                v-for="specialist in allSpecialists"
                :value="specialist.id"
                :label="specialist.full_name"
                :key="specialist.id"
              />
            </el-select>
          </div>
          <el-divider />
          <div class="d-flex">
            <el-select
              class="w-50 p-2"
              placeholder="Select Appointment Time Requirement"
              v-model="searchAppointmentForm.time_requirement"
            >
              <el-option :value="0" label="Any time" :key="0" />
              <el-option
                v-for="item in aptTimeRequireList"
                :value="item.id"
                :label="item.title"
                :key="item.id"
              />
            </el-select>
            <div class="w-50 d-flex p-2">
              <el-input
                style="width: 100px"
                type="number"
                v-model="searchAppointmentForm.timeframe_count"
                min="0"
                prop="timeframe_count"
                placeholder=""
              />
              <el-select
                class="w-100"
                placeholder="Select Time frame"
                v-model="searchAppointmentForm.timeframe_type"
              >
                <el-option value="weeks" label="week(s)" />
                <el-option value="months" label="month(s)" />
                <el-option value="years" label="year(s)" />
              </el-select>
            </div>
          </div>
          <button
            class="btn btn-primary mt-3 w-100"
            @click.prevent="handleSearch"
          >
            SEARCH
          </button>
        </el-form>
      </div>
    </div>
  </div>
  <AppointmentListPopup
    :all-specialists="allSpecialists"
    :search-next-apts="search_next_apts"
    :apt-type-list="aptTypelist"
    :clinic-list="clinic_list"
    :apt-time-require-list="aptTimeRequireList"
    v-if="allSpecialists"
  />
</template>

<script>
import { computed, defineComponent, reactive, ref } from "vue";
import moment from "moment";
import { Modal } from "bootstrap";
import { useStore } from "vuex";
import AppointmentListPopup from "@/components/appointments/AppointmentListPopup.vue";

export default defineComponent({
  name: "booing-drawer",
  components: { AppointmentListPopup },
  setup() {
    const store = useStore();
    const aptTypelist = computed(() => store.getters.getAptTypesList);
    const allSpecialists = computed(() => store.getters.getSpecialistList);

    const aptTimeRequireList = computed(
      () => store.getters.getAptTimeRequireList
    );
    const clinic_list = computed(() => store.getters.clinicsList);

    const searchAppointmentFormRef = ref(null);
    const searchAppointmentForm = ref({
      appointment_type_id: "",
      specialist_id: "",
      time_requirement: 0,
      x_weeks: "0",
      timeframe_count: 0,
      timeframe_type: "weeks",
      clinic_id: "",
    });

    const validateAppointmentTypeId = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("Please select appointment type"));
      } else {
        callback();
      }
    };
    const searchAppointmentRules = ref({
      appointment_type_id: [
        {
          required: true,
          validator: validateAppointmentTypeId,
          trigger: "blur",
        },
      ],
    });

    const search_next_apts = reactive({
      appointment_type_id: "",
      specialist_id: "",
      time_requirement: 0,
      date: moment(),
      clinic_id: "",
      x_weeks: 0,
      timeframe_count: 0,
      timeframe_type: "weeks",
    });

    const handleSearch = async () => {
      searchAppointmentFormRef.value.validate(async (valid) => {
        if (valid) {
          search_next_apts.appointment_type_id =
            searchAppointmentForm.value.appointment_type_id;
          search_next_apts.specialist_id =
            searchAppointmentForm.value.specialist_id;
          search_next_apts.time_requirement =
            searchAppointmentForm.value.time_requirement;
          search_next_apts.date = moment(moment())
            .add(
              searchAppointmentForm.value.timeframe_count,
              searchAppointmentForm.value.timeframe_type
            )
            .format("DD/MM/YYYY");
          search_next_apts.timeframe_count =
            searchAppointmentForm.value.timeframe_count;
          search_next_apts.timeframe_type =
            searchAppointmentForm.value.timeframe_type;
          search_next_apts.clinic_id = searchAppointmentForm.value.clinic_id;
          const modal = new Modal(
            document.getElementById("modal_available_time_slot_popup")
          );
          modal.show();
        }
      });
    };
    return {
      aptTypelist,
      allSpecialists,
      aptTimeRequireList,
      clinic_list,
      searchAppointmentFormRef,
      searchAppointmentForm,
      searchAppointmentRules,
      handleSearch,
      search_next_apts,
    };
  },
});
</script>
