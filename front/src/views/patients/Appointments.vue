<template>
  <CardSection heading="Appointment List">
    <template #header-actions> </template>
    <template #default>
      <Datatable
        v-if="tableData"
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="5"
        :enable-items-per-page-dropdown="true"
        highlight-today
        class="patient-appointment-table"
      >
        <template v-slot:cell-date="{ row: item }">
          <div class="p-4 d-flex flex-column">
            <span class="fw-bolder fs-1"
              >{{ item.aus_formatted_date }}
              {{ item.formatted_appointment_time }}</span
            >
            <span class="fw-bolder fs-2">{{ item.appointment_type.name }}</span>
            <span>@ {{ item.clinic_details.name }}</span>

            <span> {{ item.specialist_name }}</span>
            <span
              :class="`mb-1 p-2 rounded text-uppercase badge-xl badge-${
                item.confirmation_status === 'CONFIRMED'
                  ? 'success'
                  : item.confirmation_status === 'CANCELED'
                  ? 'danger'
                  : item.confirmation_status === 'MISSED'
                  ? 'danger'
                  : 'warning'
              } mb-2`"
              style="width: fit-content"
            >
              {{ item.confirmation_status.replace("_", " ") }}</span
            >
            <div v-if="item.confirmation_status === 'CANCELED'">
              Reason: {{ item.cancel_reason }}
            </div>
          </div>
        </template>
        <template v-slot:cell-referral="{ row: item }">
          <div
            v-if="item.referral.doctor_address_book_name"
            class="d-flex flex-column"
          >
            <div v-if="!item.is_no_referral" class="d-flex flex-column">
              <span>{{ item.referral.doctor_address_book_name }}</span>
              <span>{{ item.referral.referral_date }}</span>
              <span>{{ item.referral.referral_duration }} months</span>
            </div>
            <div v-else-if="item.is_no_referral" class="d-flex flex-column">
              <span> NO DOCTOR ADDRESS </span>
              <span>{{ item.referral.no_referral_reason }}</span>
            </div>
          </div>
          <div v-else class="d-flex flex-column">No referral information</div>
          <ViewReferralButton
            :appointment="item"
            @click="handleReferralClick(item)"
          />
        </template>
        <template v-slot:cell-attendance_status="{ row: item }">
          <div class="d-flex flex-column">
            <span
              :class="`text-uppercase badge badge-light-${
                item.attendance_status === 'not_present'
                  ? 'dark'
                  : item.attendance_status === 'waiting'
                  ? 'warning'
                  : item.attendance_status === 'checked_in'
                  ? 'success'
                  : 'primary'
              } mb-2`"
              style="width: fit-content"
            >
              {{ item.attendance_status.replace("_", " ") }}</span
            >
            <span>{{ item.collecting_person_name }}</span>
            <span>{{ item.collecting_person_phone }}</span>
            <span>{{ item.collecting_person_alternate_contact }}</span>
          </div>
          <CollectingPersonButton :appointment="item" />
        </template>
        <template v-slot:cell-report="{ row: item }">
          <div class="d-flex flex-column">
            <PatientAppointmentActions :appointment="item" :patient="patient" />
            <a
              v-if="item.procedure_approval_status !== 'NOT_RELEVANT'"
              @click="handlePreAdmissionTest(item)"
              class="btn btn-sm btn-light btn-icon-primary me-2 mb-2"
            >
              <span class="svg-icon svg-icon-1">
                <inline-svg src="media/icons/duotune/general/gen004.svg" />
              </span>
              Pre-Admission Test
            </a>
          </div>
        </template>
      </Datatable>
    </template>
  </CardSection>
  <AppointmentReferralModal
    v-if="referralAppointment"
    :appointment="referralAppointment"
  ></AppointmentReferralModal>
</template>

<style>
.patient-appointment-table * td {
  border-top: #3d749e solid 3px;
}
</style>

<script lang="ts">
import { defineComponent, watchEffect, ref, onMounted, computed } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useRouter, useRoute } from "vue-router";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import md5 from "js-md5";
import store from "@/store";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import PatientAppointmentActions from "@/components/patients/PatientAppointmentActions.vue";
import ViewReferralButton from "@/components/patients/patientAppointmentActions/ViewReferralButton.vue";
import CollectingPersonButton from "@/components/patients/patientAppointmentActions/CollectingPersonButton.vue";
import AppointmentReferralModal from "@/views/patients/modals/AppointmentReferralModal.vue";
import { Actions } from "@/store/enums/StoreEnums";

export default defineComponent({
  name: "patient-appointments",
  components: {
    Datatable,
    ViewReferralButton,
    PatientAppointmentActions,
    CollectingPersonButton,
    AppointmentReferralModal,
  },

  setup() {
    const router = useRouter();
    const route = useRoute();
    const patient = computed(() => store.getters.selectedPatient);
    const referralAppointment = ref(null);
    const userRole = computed(() => store.getters.userRole);
    const tableHeader = ref([
      {
        name: "Time/Place",
        key: "date",
        sortable: true,
      },
      {
        name: "Referring Doctor",
        key: "referral",
        sortable: false,
      },
      {
        name: "Actions",
        key: "report",
        sortable: false,
      },
    ]);
    const tableData = ref([]);

    const handlePreAdmissionTest = (item) => {
      router.push({
        path:
          "/appointment_pre_admissions/show/" +
          md5(item.id.toString()) +
          "/form_1",
      });
    };

    const handleReferralClick = (item) => {
      referralAppointment.value = item;
    };

    watchEffect(() => {
      tableData.value = patient.value.appointments;
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("Appointments", ["Patients"]);

      if (userRole.value != "specialist") {
        tableHeader.value.splice(1, 0, {
          name: "Attendance Status",
          key: "attendance_status",
          sortable: false,
        });
      }

      const id = route.params.id;
      store.dispatch(PatientActions.VIEW, id);
      store.dispatch(Actions.DOCUMENT_TEMPLATES.LIST);
    });

    return {
      tableHeader,
      tableData,
      userRole,
      referralAppointment,
      handlePreAdmissionTest,
      handleReferralClick,
      patient,
    };
  },
});
</script>
