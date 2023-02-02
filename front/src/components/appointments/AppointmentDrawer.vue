<template>
  <!--begin::Appointment Drawer drawer-->
  <div
    id="appointment-drawer"
    class="bg-white"
    data-kt-drawer="true"
    data-kt-drawer-name="appointment-drawer"
    data-kt-drawer-activate="true"
    data-kt-drawer-overlay="true"
    data-kt-drawer-width="{default:'300px', 'md': '500px'}"
    data-kt-drawer-direction="end"
    data-kt-drawer-close="#booing_edit_close"
  >
    <div class="card w-100" id="appointment-drawer">
      <!--begin::Card header-->
      <div class="card-header pe-5" id="appointment-drawer_header">
        <!--begin::Title-->
        <div class="card-title">
          <!--begin::User-->
          <h3>Appointment</h3>
          <!--end::User-->
        </div>
        <!--end::Title-->

        <!--begin::Card toolbar-->
        <div class="card-toolbar">
          <!--begin::Close-->
          <div
            class="btn btn-sm btn-icon btn-active-icon-primary"
            id="booing_edit_close"
          >
            <span class="svg-icon svg-icon-2x">
              <InlineSVG icon="cross" />
            </span>
          </div>
          <!--end::Close-->
        </div>
        <!--end::Card toolbar-->
      </div>
      <!--end::Card header-->
      <div class="card-body d-flex flex-column justify-content-between">
        <!--begin::Appointment Info-->
        <div>
          <!--begin::Approval Status Badges-->

          <AlertBadge
            v-if="appointment.procedure_approval_status === 'NOT_APPROVED'"
            :text="'This procedure has not been approved'"
            :color="'danger'"
            :iconPath="'media/icons/duotune/arrows/arr015.svg'"
          />

          <AlertBadge
            v-if="appointment.procedure_approval_status === 'NOT_ASSESSED'"
            :text="'This procedure has not yet been accessed'"
            :color="'warning'"
            :iconPath="'media/icons/duotune/arrows/arr015.svg'"
          />

          <AlertBadge
            v-if="appointment.procedure_approval_status === 'APPROVED'"
            :text="'This procedure has been approved'"
            :color="'success'"
            :iconPath="'media/icons/duotune/arrows/arr016.svg'"
          />

          <AlertBadge
            v-if="appointment.procedure_approval_status === 'CONSULT_REQUIRED'"
            :text="'This procedure requires a consult prior'"
            :color="'danger'"
            :iconPath="'media/icons/duotune/arrows/arr015.svg'"
          />

          <!--end::Approval Status Badges-->
          <!--begin::Appointment Info-->
          <div class="d-flex flex-column gap-3">
            <InfoSection :heading="'Patient'"
              >{{ patient.full_name }} ({{ patient.contact_number }})
            </InfoSection>
            <InfoSection :heading="'Time'">
              {{ appointment.formatted_appointment_time }}
            </InfoSection>
            <InfoSection :heading="'Clinic'">
              {{ appointment.clinic?.name }}
            </InfoSection>
            <InfoSection :heading="'Type'">{{
              appointment.appointment_type?.name
            }}</InfoSection>

            <InfoSection :heading="'Specialist'">{{
              appointment.specialist_name
            }}</InfoSection>

            <InfoSection :heading="'Anaesthetist'">
              <span v-if="appointment.anesthetist_name">
                {{ appointment.anesthetist_name }}
              </span>
              <span v-else> No Anaesthetist Assigned </span>
            </InfoSection>

            <InfoSection :heading="'Estimated Price'">{{
              convertToCurrency(
                appointment.appointment_type?.default_items_quote / 100
              )
            }}</InfoSection>
          </div>
          <!--end::Appointment Info-->
          <el-divider v-if="appointment.note" />
          <div v-if="appointment.note">
            <label class="fs-5 text-primary"
              >Notes:
              <span class="text-black fs-5">{{ appointment.note }}</span></label
            >
          </div>
          <el-divider />
          <div>
            <label class="fs-5 text-danger"
              >Allergies:
              <span v-if="patient.allergies?.length > 0">
                <template
                  v-for="allergy in patient.allergies"
                  :key="allergy.id"
                >
                  <span class="text-black fs-5">{{ allergy.name + " " }}</span>
                </template>
              </span>

              <span v-else> No allergies recorded </span>
            </label>
          </div>
          <el-divider />
        </div>
      </div>
      <!--end::Appointment Info-->
      <!--begin::Appointment Actions-->
      <div class="d-flex p-6 flex-column gap-3 mt-5">
        <!--Check In Button-->
        <LargeIconButton
          v-if="
            aptData.attendance_status === 'NOT_PRESENT' &&
            userRole !== 'specialist' &&
            userRole !== 'anesthetist'
          "
          @click="handleCheckIn"
          text="CHECK IN"
          :iconPath="'media/icons/duotune/arrows/arr024.svg'"
          :color="'primary'"
          justify="start"
          iconSize="3"
        />

        <!--Check Out Button-->
        <LargeIconButton
          v-if="
            aptData.attendance_status === 'CHECKED_IN' &&
            userRole !== 'specialist' &&
            userRole !== 'anesthetist'
          "
          @click="handleCheckOut"
          text="CHECK OUT"
          :iconPath="'media/icons/duotune/arrows/arr021.svg'"
          :color="'primary'"
          justify="start"
          iconSize="3"
        />
        <!--Checked Out Label-->
        <LargeIconButton
          v-if="
            aptData.attendance_status === 'CHECKED_OUT' &&
            userRole !== 'specialist' &&
            userRole !== 'anesthetist'
          "
          text="CHECKED OUT"
          :iconPath="'media/icons/duotune/arrows/arr021.svg'"
          :color="'grey'"
          justify="start"
          iconSize="3"
        />

        <!--View Patient-->
        <LargeIconButton
          @click="handleView"
          text="VIEW PATIENT"
          :iconPath="'media/icons/duotune/medicine/med001.svg'"
          :color="'primary'"
          justify="start"
          iconSize="3"
        />

        <!--Edit Appointment-->
        <LargeIconButton
          v-if="userRole !== 'specialist' && userRole !== 'anesthetist'"
          @click="handleEdit"
          text="EDIT"
          :iconPath="'media/icons/duotune/general/gen055.svg'"
          :color="'success'"
          justify="start"
          iconSize="3"
        />

        <div class="d-flex flex-row w-full">
          <!--Move Appointment-->
          <div class="col-6">
            <LargeIconButton
              class="me-1 w-100"
              v-if="userRole !== 'specialist' && userRole !== 'anesthetist'"
              @click="handleMove"
              text="MOVE"
              :iconPath="'media/icons/duotune/arrows/arr035.svg'"
              :color="'success'"
              justify="start"
              iconSize="3"
            />
          </div>
          <div class="col-6">
            <!--Move Appointment-->
            <LargeIconButton
              class="ms-1 w-100"
              v-if="userRole !== 'specialist' && userRole !== 'anesthetist'"
              @click="handleCopy"
              text="COPY"
              :iconPath="'media/icons/duotune/arrows/arr058.svg'"
              :color="'success'"
              justify="start"
              iconSize="3"
            />
          </div>
        </div>
        <template v-if="userRole == 'specialist'">
          <el-divider />
          <CreateDocumentButton
            :appointment="appointment"
            :patient="patient"
            document-type="letter"
          />
          <CreateDocumentButton
            :appointment="appointment"
            :patient="patient"
            document-type="report"
          />
          <CreateDocumentButton
            :appointment="appointment"
            :patient="patient"
            document-type="referral"
          />
        </template>
        <!--Cancel Appointment Button-->
        <LargeIconButton
          v-if="userRole !== 'specialist' && userRole !== 'anesthetist'"
          @click="handleCancel"
          text="CANCEL"
          :iconPath="'media/icons/duotune/arrows/arr011.svg'"
          :color="'danger'"
          justify="start"
          iconSize="3"
        />
      </div>
      <!--end::Appointment Actions-->
    </div>
  </div>

  <CheckInModal :appointment="aptData" :patient="patient" />
</template>

<script lang="ts">
import { defineComponent, watch, computed } from "vue";
import {
  AppointmentActions,
  AppointmentMutations,
} from "@/store/enums/StoreAppointmentEnums";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import Swal from "sweetalert2/dist/sweetalert2.min.js";
import { DrawerComponent } from "@/assets/ts/components/_DrawerComponent";
import CheckInModal from "@/components/appointments/CheckInModal.vue";
import { Modal } from "bootstrap";
import LargeIconButton from "@/components/presets/GeneralElements/LargeIconButton.vue";
import AlertBadge from "@/components/presets/GeneralElements/AlertBadge.vue";
import { convertToCurrency } from "@/core/data/billing";
import IPatient from "@/store/interfaces/IPatient";
import IAppointment from "@/store/interfaces/IAppointment";
import CreateDocumentButton from "../patients/patientAppointmentActions/CreateDocumentButton.vue";
import { Actions } from "@/store/enums/StoreEnums";

export default defineComponent({
  name: "booing-drawer",
  components: {
    CheckInModal,
    LargeIconButton,
    AlertBadge,
    CreateDocumentButton,
  },
  setup() {
    const store = useStore();
    const router = useRouter();
    const aptData = computed<IAppointment>(() => store.getters.getAptSelected);
    const appointment = computed<IAppointment>(
      () => store.getters.getAptSelected
    );

    const patient = computed<IPatient>(() => store.getters.selectedPatient);
    const searchVal = computed(() => store.getters.getSearchVariable);
    const userRole = computed(() => store.getters.userRole);

    watch(aptData, () => {
      store.dispatch(PatientActions.VIEW, aptData.value.patient_id);
      store.dispatch(Actions.DOCUMENT_TEMPLATES.LIST);
    });

    const handleView = () => {
      store.dispatch(PatientActions.VIEW, aptData.value.patient_id);
      DrawerComponent?.getInstance("appointment-drawer")?.hide();
      router.push({
        name: "patients-view-administration",
        params: { id: aptData.value.patient_id },
      });
    };

    const handleEdit = () => {
      store.dispatch(PatientActions.APPOINTMENTS, aptData.value.patient_id);
      store.commit(AppointmentMutations.SET_APT.SELECT, aptData.value);
      const modal = new Modal(document.getElementById("modal_update_apt"));
      modal.show();
      DrawerComponent?.getInstance("appointment-drawer")?.hide();
    };

    const handleMove = async () => {
      aptData.value.step = 0;
      aptData.value.action = "move";
      store.commit(AppointmentMutations.SET_APT.SELECT, aptData.value);
      const modal = new Modal(document.getElementById("modal_move_apt"));
      modal.show();
      DrawerComponent?.getInstance("appointment-drawer")?.hide();
    };

    const handleCopy = async () => {
      aptData.value.step = 0;
      aptData.value.action = "copy";
      store.commit(AppointmentMutations.SET_APT.SELECT, aptData.value);
      const modal = new Modal(document.getElementById("modal_move_apt"));
      modal.show();
      DrawerComponent?.getInstance("appointment-drawer")?.hide();
    };

    const handleCancel = () => {
      const html =
        "<h3>Are you sure you want to cancel?</h3><br/>" +
        '<h4><input type="checkbox" id="chkMissed"> ' +
        '<label for="chkMissed">Mark as Missed</label></h4>';
      Swal.fire({
        input: "text",
        inputAttributes: {
          autocapitalize: "off",
          placeholder: "Enter the Reason",
        },
        html: html,
        icon: "warning",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Confirm",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light-primary",
        },
        preConfirm: async (data) => {
          var missed = Swal.getPopup().querySelector("#chkMissed").checked;
          await store
            .dispatch(AppointmentActions.CONFIRMATION_STATUS.UPDATE, {
              id: aptData.value.id,
              missed: missed,
              reason: data,
            })
            .then(() => {
              store
                .dispatch(AppointmentActions.LIST, {
                  date: searchVal.value.date,
                })
                .then(() => {
                  store.dispatch(
                    AppointmentActions.BOOKING.SEARCH.SPECIALISTS,
                    searchVal.value
                  );
                  DrawerComponent?.getInstance("appointment-drawer")?.hide();
                });
            });
        },
      });
    };

    const handleCheckIn = () => {
      const modal = new Modal(document.getElementById("modal_check_in_apt"));
      modal.show();
    };

    const handleCheckOut = async () => {
      await store
        .dispatch(AppointmentActions.APT.CHECK_OUT, aptData.value)
        .then(() => {
          store.dispatch(
            AppointmentActions.BOOKING.SEARCH.SPECIALISTS,
            searchVal.value
          );
          Swal.fire({
            text: "Successfully Checked Out!",
            icon: "success",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
              confirmButton: "btn btn-primary",
            },
          }).then(() => {
            DrawerComponent?.getInstance("appointment-drawer")?.hide();
          });
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    };

    return {
      aptData,
      handleEdit,
      handleView,
      handleCancel,
      handleCheckIn,
      handleCheckOut,
      handleMove,
      handleCopy,
      userRole,
      convertToCurrency,
      patient,
      appointment,
    };
  },
});
</script>
