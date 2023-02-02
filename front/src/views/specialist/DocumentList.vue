<template>
  <div class="row">
    <div class="col-md-4">
      <el-input
        v-model="documentTitleFilter"
        class="w-100 mb-6"
        placeholder="Please input search title"
      />
      <!-- DOCUMENT TYPE FILTER SELECT-->
      <el-select
        class="w-100 mb-6"
        placeholder="Select Document Type"
        v-model="documentTypeFilter"
      >
        <el-option value="ALL" label="ALL DOCUMENT TYPES">
          <inline-svg
            class="me-5"
            src="media/icons/duotune/general/gen054.svg"
          />
          ALL DOCUMENT TYPES
        </el-option>
        <template v-for="type in patientDocumentTypes" :key="type.value">
          <el-option :value="type.value" :label="type.label">
            <inline-svg class="me-5" :src="type.icon" />
            {{ type.label }}
          </el-option>
        </template>
      </el-select>
      <!-- APPOINTMENT FILTER SELECT-->
      <el-select
        v-if="appointments"
        class="filter-appointment w-100 pb-6"
        placeholder="Select Appointment"
        v-model="appointmentFilter"
      >
        <el-option value="ALL" label="ALL APPOINTMENTS">
          ALL Appointments
        </el-option>
        <template v-for="appointment in appointments" :key="appointment.id">
          <el-option
            :value="appointment.id"
            :label="
              appointment.aus_formatted_date +
              ' ' +
              appointment.formatted_appointment_time +
              ' , ' +
              appointment.specialist_name
            "
          >
            {{ appointment.aus_formatted_date }}
            {{ appointment.formatted_appointment_time }}
            , {{ appointment.appointment_type_name }} ,
            <span>{{ appointment.specialist_name }}</span>
          </el-option>
        </template>
      </el-select>
      <!-- SPECIALIST FILTER SELECT-->
      <el-select
        v-if="specialists"
        class="filter-appointment w-100 pb-6"
        placeholder="Select Specialist"
        v-model="specialistFilter"
      >
        <el-option value="ALL" label="ALL SPECIALISTS">
          ALL Specialists
        </el-option>
        <template v-for="specialist in specialists" :key="specialist.id">
          <el-option
            :value="specialist.id"
            :label="specialist.first_name + ' ' + specialist.last_name"
          >
          </el-option>
        </template>
      </el-select>
      <div class="pb-5">
        <el-switch
          v-model="showUrgentOnly"
          size="large"
          active-text="Show Urgent Only"
          inactive-text=""
        />
        <el-switch
          class="mx-4"
          v-model="showUnReadOnly"
          size="large"
          active-text="Show UnRead Only"
          inactive-text=""
        />
      </div>
      <div
        v-if="documentList?.length === 0"
        class="d-flex justify-content-center align-items-center fs-3"
      >
        No Documents Exist
      </div>
      <div class="d-flex flex-column h-450px scroll">
        <div v-for="document in filteredDocuments" :key="document.id">
          <input
            type="radio"
            class="btn-check"
            :name="document.id"
            :value="document.id"
            :id="document.id"
            v-model="selectedDocumentId"
          />
          <DocumentLabel :condensed="condensed" :document="document" />
        </div>
      </div>
    </div>
    <div class="col-md-8 d-flex flex-column">
      <div class="d-flex flex-row" v-if="selectedDocument">
        <!-- DOCUMENT INFO -->
        <div class="d-flex p-6 flex-column" v-if="showDocumentInformation">
          <InfoSection heading="Patient">
            {{ selectedDocument.document_info.patient }}
            <IconButton
              @click="showAssignPatientModal()"
              v-if="!selectedDocument.document_info.patient"
              label="Assign Patient"
            />
          </InfoSection>
          <InfoSection heading="Specialist"
            >{{ selectedDocument.document_info.specialist }}
            <IconButton
              @click="showAssignSpecialistModal()"
              v-if="!selectedDocument.document_info.specialist"
              label="Assign Specialist"
            />
          </InfoSection>
          <InfoSection heading="Appointment"
            >{{ selectedDocument.document_info.appointment }}
            <IconButton
              v-if="
                selectedDocument.document_info.patient &&
                !selectedDocument.document_info.appointment
              "
              @click="showAssignAppointmentModal()"
              label="Assign Appointment"
            />
            <IconButton
              class="disabled"
              v-if="
                !selectedDocument.document_info.patient &&
                !selectedDocument.document_info.appointment
              "
              label="Assign Appointment"
            />
          </InfoSection>
        </div>
        <!-- DOCUMENT ACTIONS -->
        <div class="d-flex p-6 flex-column" v-if="showDocumentActions">
          <IconButton class="mb-2" label="Print" @click="handlePrint" />
          <IconButton class="mb-2" label="Email" @click="handleSendEmail" />
          <IconButton
            class="mb-2"
            label="HealthLink"
            @click="handleSendHealthLink"
          />
          <IconButton
            class="mb-2"
            v-if="userRole == 'specialist' && !selectedDocument.is_read"
            label="Mark Read"
            @click="handleMarkRead(true)"
          />
          <IconButton
            class="mb-2"
            v-if="userRole == 'specialist' && selectedDocument.is_read"
            label="Mark UnRead"
            @click="handleMarkRead(false)"
          />
          <IconButton
            class="mb-2"
            v-if="userRole == 'specialist' && !selectedDocument.is_urgent"
            label="Mark Urgent"
            @click="handleMarkUrgent(true)"
          />
          <IconButton
            class="mb-2"
            v-if="userRole == 'specialist' && selectedDocument.is_urgent"
            label="Mark Not Urgent"
            @click="handleMarkUrgent(false)"
          />
          <IconButton
            class="mb-2"
            v-if="
              userRole == 'specialist' &&
              !selectedDocument.is_incorrectly_assigned
            "
            label="Mark Incorrectly assigned"
            @click="handleMarkCorrect(true)"
          />
          <IconButton
            class="mb-2"
            v-if="
              userRole == 'specialist' &&
              selectedDocument.is_incorrectly_assigned
            "
            label="Mark correctly assigned"
            @click="handleMarkCorrect(false)"
          />
        </div>
        <!-- DOCUMENT VIEW DIV -->
        <div class="d-flex p-6 flex-column">
          <IconButton class="mb-2" label="View logs" @click="handleViewLogs" />
        </div>
      </div>
      <div class="h-450px" id="documentField">
        <div class="fv-row pdf_viewer_wrapper">
          <div id="document-view" class="pdf_viewer"></div>
        </div>
      </div>
    </div>
  </div>

  <SendDocumentViaEmailModal
    v-if="selectedDocument"
    :document="selectedDocument"
  ></SendDocumentViaEmailModal>
  <SendDocumentViaHealthLinkModal
    v-if="selectedDocument"
    :document="selectedDocument"
  ></SendDocumentViaHealthLinkModal>
  <DocumentActionLogModal
    v-if="selectedDocument"
    :document="selectedDocument"
  ></DocumentActionLogModal>
  <AssignPatientModal
    :document="selectedDocument"
    :handle-set-selected-document="handleSetSelectedDocument"
  ></AssignPatientModal>
  <AssignSpecialistModal
    :document="selectedDocument"
    :handle-set-selected-document="handleSetSelectedDocument"
  ></AssignSpecialistModal>
  <AssignAppointmentModal
    :document="selectedDocument"
    :handle-set-selected-document="handleSetSelectedDocument"
  ></AssignAppointmentModal>
</template>
<style lang="scss">
.pdf_viewer_wrapper {
  height: 100%;
  > .pdf_viewer {
    height: 100%;
    overflow: auto;
  }
}
.filter-appointment {
  width: calc(100% - 215px);
}
</style>

<script>
import { defineComponent, computed, watch, ref, watchEffect } from "vue";
import { useStore } from "vuex";
import pdf from "pdfobject";
import patientDocumentTypes from "@/core/data/patient-document-types";
import patientDocumentActionStatus from "@/core/data/patient-document-action-status";
import { DocumentActions } from "@/store/enums/StoreDocumentEnums";
import { Actions } from "@/store/enums/StoreEnums";
import DocumentLabel from "@/views/patients/documents/DocumentLabel.vue";
import SendDocumentViaEmailModal from "@/views/patients/documents/SendDocumentViaEmailModal.vue";
import SendDocumentViaHealthLinkModal from "@/views/patients/documents/SendDocumentViaHealthLinkModal.vue";

import DocumentActionLogModal from "@/views/patients/documents/DocumentActionLogModal.vue";
import { Modal } from "bootstrap";
import AssignPatientModal from "@/views/specialist/modals/AssignPatientModal.vue";
import AssignSpecialistModal from "@/views/specialist/modals/AssignSpecialistModal.vue";
import AssignAppointmentModal from "@/views/specialist/modals/AssignAppointmentModal.vue";

import Swal from "sweetalert2/dist/sweetalert2.min.js";

export default defineComponent({
  name: "admin-main",

  components: {
    DocumentLabel,
    SendDocumentViaEmailModal,
    SendDocumentViaHealthLinkModal,
    DocumentActionLogModal,
    AssignPatientModal,
    AssignSpecialistModal,
    AssignAppointmentModal,
  },
  props: {
    condensed: { default: true },
    showDocumentInformation: { default: true },
    showDocumentActions: { default: true },
    appointments: { required: false },
  },
  setup() {
    const store = useStore();

    const currentUser = computed(() => store.getters.currentUser);
    const selectedPatient = computed(() => store.getters.selectedPatient);
    const documents = computed(() => store.getters.documentsList);
    const selectedDocumentData = computed(
      () => store.getters.getSelectedDocument
    );
    const userRole = computed(() => store.getters.userRole);
    const specialists = computed(() => store.getters.getSpecialistList);

    const filteredDocuments = ref();

    const documentTitleFilter = ref("");
    const documentTypeFilter = ref("ALL");
    const appointmentFilter = ref("ALL");
    const specialistFilter = ref("ALL");

    const tempFile = ref();
    var selectedDocument = ref(null);
    var selectedDocumentId = ref(null);
    const showDocumentDetail = ref(true);

    var showUrgentOnly = ref(false);
    var showUnReadOnly = ref(false);

    // Filters the documents by appointment and document type.
    watch(
      [
        documentTitleFilter,
        documentTypeFilter,
        appointmentFilter,
        specialistFilter,
        showUrgentOnly,
        showUnReadOnly,
        documents,
      ],
      () => {
        document.getElementById("document-view").innerHTML = "";

        let temp = documents.value;
        if (documentTypeFilter.value !== "ALL") {
          temp = documents.value.filter(
            (item) => item.document_type === documentTypeFilter.value
          );
        }

        if (documentTitleFilter.value) {
          temp = temp.filter(
            (item) =>
              item.document_name
                .toLowerCase()
                .indexOf(documentTitleFilter.value.toLowerCase()) > -1
          );
        }

        if (appointmentFilter.value !== "ALL") {
          temp = temp?.filter(
            (item) => item.appointment_id === appointmentFilter.value
          );
        }

        if (specialistFilter.value !== "ALL") {
          temp = temp?.filter(
            (item) => item.specialist_id === specialistFilter.value
          );
        }

        if (showUrgentOnly.value) {
          temp = temp?.filter((item) => item.is_urgent === 1);
        }

        if (showUnReadOnly.value) {
          temp = temp?.filter((item) => item.is_read === 0);
        }

        filteredDocuments.value = temp;
      }
    );

    const setSelectedDocument = () => {
      const temp = documents.value.filter(
        (item) => item.id === selectedDocumentId.value
      );
      selectedDocument.value = temp && temp.length > 0 ? temp[0] : null;
    };

    const setSelectedDocumentId = () => {
      if (selectedDocumentData.value && selectedDocumentData.value.id) {
        const temp = documents.value.filter(
          (item) => item.id === selectedDocumentData.value.id
        );
        selectedDocumentId.value =
          temp && temp.length > 0 ? selectedDocumentData.value.id : null;
      }
    };
    watch(selectedDocumentId, () => {
      if (selectedDocumentId.value) {
        setSelectedDocument();
      }
    });

    watch(selectedPatient, () => {
      if (selectedPatient.value) {
        store
          .dispatch(DocumentActions.LIST, {
            patient_id: selectedPatient.value.id,
          })
          .then(() => {
            setSelectedDocumentId();
          });
      }
    });

    watch([currentUser, specialists], () => {
      // if (currentUser.value && specialists.value?.length > 0) {
      //   if (
      //     specialists.value.some(
      //       (item) => item.id === currentUser.value.profile.id
      //     )
      //   )
      //     specialistFilter.value = currentUser.value.profile.id;
      // }
    });

    // Loads the selected document from the server to the view window
    watch(selectedDocument, () => {
      if (selectedDocument.value) {
        if (selectedDocument.value.file_type === "HTML") {
          document.getElementById("document-view").innerHTML =
            selectedDocument.value.document_body;
        } else {
          store
            .dispatch(Actions.FILE.VIEW, {
              path: selectedDocument.value.file_path,
            })
            .then((data) => {
              tempFile.value = data;
              if (selectedDocument.value.file_type === "PDF") {
                document.getElementById("document-view").innerHTML = "";
                let blob = new Blob([data], { type: "application/pdf" });
                let objectUrl = URL.createObjectURL(blob);
                pdf.embed(objectUrl + "#toolbar=0", "#document-view");
              } else if (selectedDocument.value.file_type === "PNG") {
                document.getElementById("document-view").innerHTML =
                  "<img src='" + data + "' />";
              }
            })
            .catch(() => {
              console.log("Document Load Error");
            });
        }
      }
    });

    const handleSendEmail = () => {
      const modal = new Modal(document.getElementById("modal_send_email"));
      modal.show();
    };

    const handleSendHealthLink = () => {
      const modal = new Modal(
        document.getElementById("modal_send_health_link")
      );
      modal.show();
    };

    const handlePrint = () => {
      if (selectedDocument.value.file_type === "PDF") {
        var blob = new Blob([tempFile.value], { type: "application/pdf" });
        var blobURL = URL.createObjectURL(blob);

        let iframe = document.createElement("iframe");
        document.body.appendChild(iframe);

        iframe.style.display = "none";
        iframe.src = blobURL;
        let Data = new FormData();
        Data.append("patient_document_id", selectedDocument.value.id);
        Data.append("status", patientDocumentActionStatus.PRINTED);
        store.dispatch(DocumentActions.ACTION_LOGS.CREATE, {
          patient_document_id: selectedDocument.value.id,
          data: Data,
        });
        iframe.onload = function () {
          setTimeout(function () {
            iframe.focus();
            iframe.contentWindow.print();
          }, 1);
        };
      }
    };

    const handleViewLogs = () => {
      store.dispatch(Actions.OUTGOING.LIST, {
        document_id: selectedDocument.value.id,
      });
      const modal = new Modal(
        document.getElementById("modal_document_action_log")
      );
      modal.show();
    };

    const showAssignPatientModal = () => {
      const modal = new Modal(document.getElementById("modal_assign_patient"));
      modal.show();
    };

    const showAssignSpecialistModal = () => {
      const modal = new Modal(
        document.getElementById("modal_assign_specialist")
      );
      modal.show();
    };

    const showAssignAppointmentModal = () => {
      const modal = new Modal(
        document.getElementById("modal_assign_appointment")
      );
      modal.show();
    };

    const handleSetSelectedDocument = (flag = null) => {
      if (
        selectedDocument &&
        selectedDocument.value &&
        selectedDocument.value.id
      ) {
        if (flag === "PATIENT") {
          selectedDocument.value = documents.value.find(
            (doc) => doc.id === selectedDocument.value.id
          );
        } else if (flag === "SPECIALIST") {
          if (
            selectedDocument.value.patient_id &&
            selectedDocument.value.appointment_id
          ) {
            selectedDocumentId.value = null;
          } else {
            selectedDocument.value = documents.value.find(
              (doc) => doc.id === selectedDocument.value.id
            );
          }
        } else if (flag === "APPOINTMENT") {
          if (
            selectedDocument.value.patient_id &&
            selectedDocument.value.specialist_id
          ) {
            selectedDocumentId.value = null;
          } else {
            selectedDocument.value = documents.value.find(
              (doc) => doc.id === selectedDocument.value.id
            );
          }
        } else {
          const tempId = selectedDocumentId.value;
          selectedDocumentId.value = null;
          // selectedDocument.value = null;
          setTimeout(() => {
            selectedDocumentId.value = tempId;
          }, 500);
        }
      }
    };

    const handleMarkRead = (flag) => {
      const html =
        "<h3>Are you sure you want to mark as " +
        (flag ? '"Read"' : '"UnRead"') +
        "?</h3>";
      Swal.fire({
        html: html,
        icon: "warning",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Confirm",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light-primary",
        },
        preConfirm: async () => {
          store
            .dispatch(DocumentActions.UPDATE, {
              is_read: flag ? 1 : 0,
              document_id: selectedDocument.value.id,
              document_type: selectedDocument.value.document_type,
              document_name: selectedDocument.value.document_name,
            })
            .then(() => {
              store
                .dispatch(DocumentActions.LIST, {
                  is_missing_information: 1,
                  origin: "RECEIVED",
                })
                .then(() => {
                  setTimeout(() => {
                    handleSetSelectedDocument();
                  }, 200);
                });
            });
        },
      });
    };

    const handleMarkUrgent = (flag) => {
      const html =
        "<h3>Are you sure you want to mark as " +
        (flag ? '"Urgent"' : '"Not Urgent"') +
        "?</h3>";
      Swal.fire({
        html: html,
        icon: "warning",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Confirm",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light-primary",
        },
        preConfirm: async () => {
          store
            .dispatch(DocumentActions.UPDATE, {
              is_urgent: flag ? 1 : 0,
              document_id: selectedDocument.value.id,
              document_type: selectedDocument.value.document_type,
              document_name: selectedDocument.value.document_name,
            })
            .then(() => {
              store
                .dispatch(DocumentActions.LIST, {
                  is_missing_information: 1,
                  origin: "RECEIVED",
                })
                .then(() => {
                  setTimeout(() => {
                    handleSetSelectedDocument();
                  }, 200);
                });
            });
        },
      });
    };

    const handleMarkCorrect = (flag) => {
      const html =
        "<h3>Are you sure you want to mark as " +
        (flag ? '"Incorrectly Assigned"' : '"Correctly Assigned"') +
        "?</h3>";
      Swal.fire({
        html: html,
        icon: "warning",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Confirm",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light-primary",
        },
        preConfirm: async () => {
          store
            .dispatch(DocumentActions.UPDATE, {
              is_incorrectly_assigned: flag ? 1 : 0,
              document_id: selectedDocument.value.id,
              document_type: selectedDocument.value.document_type,
              document_name: selectedDocument.value.document_name,
            })
            .then(() => {
              store
                .dispatch(DocumentActions.LIST, {
                  is_missing_information: 1,
                  origin: "RECEIVED",
                })
                .then(() => {
                  setTimeout(() => {
                    handleSetSelectedDocument();
                  }, 200);
                });
            });
        },
      });
    };

    watchEffect(() => {
      if (specialists.value.length === 0) {
        store.dispatch(Actions.SPECIALIST.LIST);
      }
    });

    return {
      patientDocumentTypes,
      patientDocumentActionStatus,
      DocumentLabel,
      filteredDocuments,
      documentTitleFilter,
      documentTypeFilter,
      appointmentFilter,
      selectedDocument,
      handlePrint,
      handleSendEmail,
      handleSendHealthLink,
      handleViewLogs,
      SendDocumentViaEmailModal,
      showAssignPatientModal,
      showAssignSpecialistModal,
      showAssignAppointmentModal,
      handleSetSelectedDocument,
      selectedDocumentId,
      showDocumentDetail,
      userRole,

      handleMarkRead,
      handleMarkUrgent,
      handleMarkCorrect,

      showUrgentOnly,
      showUnReadOnly,
      specialists,
      specialistFilter,
    };
  },
});
</script>
