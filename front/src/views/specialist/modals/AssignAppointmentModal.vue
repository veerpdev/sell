<template>
  <ModalWrapper
    title="Assign Appointments"
    modalId="assign_appointment"
    :updateRef="updateRef"
  >
    <div class="row g-8">
      <Datatable
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="5"
        :key="tableKey"
        :loading="loading"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-date="{ row: item }">
          <div class="p-4 d-flex flex-column">
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
            <span
              >{{ item.aus_formatted_date }}
              {{ item.formatted_appointment_time }}</span
            >
            <span>@ {{ item.clinic_details.name }}</span>

            <span>{{ item.appointment_type.name }}</span>
            <span> {{ item.specialist_name }}</span>
          </div>
        </template>
        <template v-slot:cell-referral="{ row: item }">
          <div
            v-if="item.referral && item.referral.doctor_address_book_name"
            class="d-flex flex-column"
          >
            <div v-if="!item.is_no_referral" class="d-flex flex-column">
              <span>{{ item.referral.doctor_address_book_name }}</span>
              <span>{{ item.referral.referral_date }}</span>
              <span>{{ item.referral.referral_duration }} months</span>
            </div>
            <div v-else-if="item.is_no_referral" class="d-flex flex-column">
              <span> NO REFERRAL </span>
              <span>{{ item.referral.no_referral_reason }}</span>
            </div>
          </div>
          <div v-else class="d-flex flex-column">No referral information</div>
          <button
            class="btn btn-bg-light btn-active-color-primary btn-sm mt-2"
            @click="handleReferral(item)"
          >
            Update Referral
          </button>
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
          <button
            class="btn btn-bg-light btn-active-color-primary btn-sm mt-2"
            @click="handleCollectingPerson(item)"
          >
            Update Collecting Person
          </button>
        </template>
        <template v-slot:cell-report="{ row: item }">
          <div class="d-flex flex-column">
            <button
              @click="handleAssign(item)"
              class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
            >
              <span class="svg-icon svg-icon-3">
                <em class="bi bi-check-circle"></em>
              </span>
            </button>
          </div>
        </template>
      </Datatable>
    </div>
  </ModalWrapper>
</template>

<script>
import { defineComponent, ref, reactive, watch, computed } from "vue";
import { useStore } from "vuex";
import { DocumentActions } from "@/store/enums/StoreDocumentEnums";
import { hideModal } from "@/core/helpers/dom";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";

export default defineComponent({
  components: {
    Datatable,
  },
  name: "assign-specialist-modal",
  props: {
    document: { type: Object, required: true },
    handleSetSelectedDocument: { type: Function, required: true },
  },
  setup(props) {
    const store = useStore();
    const list = computed(() => store.getters.getAptList);
    const selectedDocument = computed(() => props.document);
    const documentId = computed(() => props.document.id);
    const documentType = computed(() => props.document.document_type);
    const documentName = computed(() => props.document.document_name);
    const loading = ref(false);
    const assignAppointmentModalRef = ref(null);

    const filter = reactive({
      first_name: "",
      last_name: "",
      date_of_birth: "",
    });

    const tableHeader = ref([
      {
        name: "Time/Place",
        key: "date",
        sortable: true,
      },
      {
        name: "Referral",
        key: "referral",
        sortable: false,
      },
      {
        name: "Attendance Status",
        key: "attendance_status",
        sortable: false,
      },
      {
        name: "Actions",
        key: "report",
        sortable: false,
      },
    ]);

    const tableData = ref([]);
    const tableKey = ref(0);
    const renderTable = () => tableKey.value++;
    const clearFilters = () => {
      filter.first_name = "";
      filter.last_name = "";
      filter.date_of_birth = "";
      renderTable();
    };

    const searchAppointments = (params) => {
      loading.value = true;
      store.dispatch(AppointmentActions.LIST, params).then(() => {
        loading.value = false;
      });
    };

    const updateRef = (_ref) => {
      assignAppointmentModalRef.value = _ref;
    };

    const handleAssign = (appointment) => {
      store
        .dispatch(DocumentActions.UPDATE, {
          appointment_id: appointment.id,
          document_id: documentId.value,
          document_type: documentType.value,
          document_name: documentName.value,
        })
        .then(() => {
          clearFilters();
          tableData.value = [];
          renderTable();
          store
            .dispatch(DocumentActions.LIST, {
              is_missing_information: 1,
              origin: "RECEIVED",
            })
            .then(() => {
              setTimeout(() => {
                props.handleSetSelectedDocument("APPOINTMENT");
                hideModal(assignAppointmentModalRef.value);
              }, 200);
            });
        });
    };

    watch(list, () => {
      tableData.value = list.value;
      renderTable();
    });

    watch(selectedDocument, () => {
      selectedDocument.value &&
        selectedDocument.value.patient &&
        searchAppointments({
          patient_id: selectedDocument.value.patient.id,
          specialist_id: selectedDocument.value.specialist?.id,
        });
    });

    return {
      filter,
      searchAppointments,
      clearFilters,
      assignAppointmentModalRef,
      updateRef,
      tableKey,
      tableHeader,
      tableData,
      loading,
      handleAssign,
      selectedDocument,
    };
  },
});
</script>
