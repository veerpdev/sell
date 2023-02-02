<template>
  <div class="card w-xxl-75 m-auto">
    <div class="card-header p-5">
      <button
        type="button"
        class="text-nowrap btn btn-light-primary ms-auto"
        :disabled="loading"
        @click.prevent="handleAddPatient"
      >
        <span class="svg-icon svg-icon-2">
          <InlineSVG icon="plus" />
        </span>
        Add Patient
      </button>
    </div>
    <div class="card-header border-0 p-5">
      <div class="card border border-dashed border-primary w-100">
        <div class="card-body">
          <div class="card-info">
            <el-form class="w-100" ref="formRef_1">
              <!--begin::Row-->
              <div class="row g-8">
                <!--begin::Col-->
                <div class="col-lg-4">
                  <label class="fs-6 form-label fw-bolder text-dark"
                    >First Name</label
                  >
                  <el-form-item prop="first_name">
                    <el-input
                      type="text"
                      v-model="filter.first_name"
                      placeholder="First Name"
                    />
                  </el-form-item>
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-lg-4">
                  <label class="fs-6 form-label fw-bolder text-dark"
                    >Last Name</label
                  >
                  <el-form-item prop="last_name">
                    <el-input
                      type="text"
                      v-model="filter.last_name"
                      placeholder="Last Name"
                    />
                  </el-form-item>
                </div>
                <!--begin::Col-->
                <div class="col-lg-4">
                  <label class="fs-6 form-label fw-bolder text-dark"
                    >Date of Birth</label
                  >
                  <el-form-item prop="date">
                    <el-date-picker
                      class="w-100"
                      v-model="filter.date_of_birth"
                      format="DD/MM/YYYY"
                      placeholder="01/01/1990"
                    />
                  </el-form-item>

                  <div
                    class="d-flex align-items-center justify-content-end mt-5"
                  >
                    <button
                      type="submit"
                      class="btn btn-primary me-5 w-50"
                      :disabled="
                        filter.first_name.length < 2 &&
                        filter.last_name.length < 2 &&
                        filter.date_of_birth.length < 2
                      "
                      @click.prevent="searchPatient"
                    >
                      SEARCH
                    </button>
                    <button
                      type="submit"
                      class="btn btn-light-primary w-50"
                      :disabled="
                        filter.first_name.length === 0 &&
                        filter.last_name.length === 0 &&
                        filter.date_of_birth.length === 0
                      "
                      @click="clearFilters"
                    >
                      CLEAR
                    </button>
                  </div>
                </div>
                <!--end::Col-->
              </div>
              <!--end::Row-->
            </el-form>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body pt-0">
      <Datatable
        v-if="tableData"
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="5"
        :loading="loading"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-full_name="{ row: item }">
          <span class="text-dark fw-bolder text-hover-primary mb-1 fs-6">
            <button
              @click="handleView(item)"
              class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
            >
              <span class="svg-icon svg-icon-3">
                <em class="fas fa-eye"></em>
              </span>
            </button>
            {{ item.first_name }} {{ item.last_name }}
          </span>
        </template>
        <template v-slot:cell-dob="{ row: item }">
          <span class="text-dark fw-bolder text-hover-primary mb-1 fs-6">
            {{ new Date(item.date_of_birth).toLocaleDateString("en-AU") }}
          </span>
        </template>
        <template v-slot:cell-contact_number="{ row: item }">
          {{ item.contact_number }}
        </template>
        <template
          v-if="userRole != 'specialist'"
          v-slot:cell-upcoming="{ row: item }"
        >
          <span
            v-for="upcoming_appointment in item.upcoming_appointments"
            :key="upcoming_appointment.id"
            :class="`badge ${
              upcoming_appointment.id ? '' : 'badge-light-success'
            }`"
            style="color: #000000"
            :style="`width: fit-content; background-color: ${
              upcoming_appointment.id
                ? upcoming_appointment.appointment_type.color
                : ''
            }; cursor: ${upcoming_appointment.id ? 'pointer' : 'not-allowed'}`"
            @click="
              upcoming_appointment.id ? handleBadge(upcoming_appointment) : ''
            "
          >
            {{
              upcoming_appointment.id
                ? upcoming_appointment.date +
                  " " +
                  upcoming_appointment.start_time +
                  "(" +
                  upcoming_appointment.appointment_type.name +
                  ")"
                : "none"
            }}</span
          >
        </template>
      </Datatable>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref, computed, watch } from "vue";
import { useRouter } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import { AppointmentMutations } from "@/store/enums/StoreAppointmentEnums";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { DrawerComponent } from "@/assets/ts/components/_DrawerComponent";
import store from "@/store";
import IPatient from "@/store/interfaces/IPatient";

export default defineComponent({
  name: "patients-list",

  components: {
    Datatable,
  },

  setup() {
    const router = useRouter();
    const tableHeader = ref([
      {
        name: "Full Name",
        key: "full_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Date of Birth",
        key: "dob",
        sortable: true,
        searchable: true,
      },
      {
        name: "Contact Number",
        key: "contact_number",
        sortable: true,
        searchable: true,
      },
      {
        name: "Upcoming Appointments",
        key: "upcoming",
      },
    ]);

    const userRole = computed(() => store.getters.userRole);
    const patientData = ref(computed(() => store.getters.patientsList));
    const tableData = ref<IPatient[]>();
    const loading = ref<boolean>(false);

    watch(patientData, () => {
      tableData.value = patientData.value;
      loading.value = false;
    });

    onMounted(() => {
      store.dispatch(PatientActions.LIST);
    });

    const filter = ref({
      first_name: "",
      last_name: "",
      date_of_birth: "",
    });

    const searchPatient = () => {
      loading.value = true;
      store.dispatch(PatientActions.LIST, {
        first_name: filter.value.first_name,
        last_name: filter.value.last_name,
        date_of_birth: filter.value.date_of_birth,
      });
    };

    const clearFilters = () => {
      filter.value.first_name = "";
      filter.value.last_name = "";
      filter.value.date_of_birth = "";
    };

    const handleView = (item) => {
      store.dispatch(PatientActions.VIEW, item.id);
      router.push({
        name: "patients-view-appointments",
        params: { id: item.id },
      });
    };

    const handleBadge = (upcoming_appointment) => {
      store.commit(AppointmentMutations.SET_APT.SELECT, upcoming_appointment);
      DrawerComponent?.getInstance("appointment-drawer")?.setBookingDrawerShown(
        true
      );
      router.push({ name: "booking-dashboard" });
    };

    const handleAddPatient = () => {
      router.push({ name: "add-patient" });
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Patients", []);
    });

    return {
      tableHeader,
      tableData,
      filter,
      handleView,
      handleBadge,
      searchPatient,
      clearFilters,
      loading,
      userRole,
      handleAddPatient,
    };
  },
});
</script>
