<template>
  <div
    class="modal fade"
    id="modal_bulk_move_apt"
    aria-hidden="true"
    ref="bulkMoveAptModalRef"
  >
    <div
      :class="
        'modal-dialog modal-dialog-centered ' +
        (step === 0 ? 'mw-1000px' : 'mw-1000px')
      "
    >
      <div class="modal-content">
        <div class="modal-header" id="kt_modal_move_appointment_header">
          <div class="d-block" v-if="step === 0">
            <h2 class="fw-bolder">Bulk Move Appointments:</h2>
          </div>
          <h2 class="select-new-apt-caption" v-if="step === 1">
            Please Confirm Changes
          </h2>
          <div class="d-flex justify-content-end w-600px" v-if="step === 1">
            <button
              class="btn btn-primary"
              v-if="step === 1"
              @click.prevent="handleBack"
            >
              Back
            </button>
          </div>
          <div
            id="kt_modal_add_customer_close"
            data-bs-dismiss="modal"
            class="btn btn-icon btn-sm btn-active-icon-primary"
          >
            <span class="svg-icon svg-icon-1">
              <InlineSVG icon="cross" />
            </span>
          </div>
        </div>
        <div class="modal-body py-10 px-lg-8">
          <div
            class="scroll-y me-n7 pe-7"
            id="kt_modal_move_appointment_scroll"
            data-kt-scroll="true"
            data-kt-scroll-activate="{default: false, lg: true}"
            data-kt-scroll-max-height="auto"
            data-kt-scroll-dependencies="#kt_modal_move_appointment_header"
            data-kt-scroll-wrappers="#kt_modal_move_appointment_scroll"
            data-kt-scroll-offset="300px"
          >
            <el-form
              :model="formData"
              ref="formRef"
              v-if="step === 0"
              :rules="rules"
              v-loading="loading"
            >
              <div class="row">
                <div class="col-6 apt-card">
                  <el-form-item prop="specialist_id_from">
                    <el-select
                      class="w-100"
                      placeholder="Select Specialist"
                      v-model="formData.specialist_id_from"
                      filterable
                      @change="getAppointmentsFrom"
                    >
                      <el-option value="" label="Any Specialist" />
                      <el-option
                        v-for="specialist in allSpecialist"
                        :value="specialist.id"
                        :label="specialist?.full_name"
                        :key="specialist.id"
                      />
                    </el-select>
                  </el-form-item>
                  <el-form-item class="col-6" prop="from_date">
                    <el-date-picker
                      v-model="formData.from_date"
                      type="date"
                      placeholder="Pick a day"
                      :editable="false"
                      :clearable="false"
                      @change="getAppointmentsFrom()"
                    />
                  </el-form-item>
                  <div class="row">
                    <el-popover
                      placement="left"
                      :width="250"
                      title="Warning !"
                      trigger="click"
                    >
                      <template #reference>
                        <el-form-item label="Allow Double Booking">
                          <el-switch
                            ref=""
                            v-model="formData.allowDoubleBooking"
                          />
                        </el-form-item>
                      </template>
                      <p>This will overlap with other appointments</p>
                    </el-popover>
                    <el-form-item label="Match Appointment Time">
                      <el-switch v-model="formData.matchAppointmentTime" />
                    </el-form-item>
                    <el-form-item label="Match appointment restrictions">
                      <el-switch
                        v-model="formData.matchAppointmentRestrictions"
                      />
                    </el-form-item>
                  </div>
                </div>

                <div class="col-6 apt-card">
                  <el-form-item prop="specialist_id_to">
                    <el-select
                      class="w-100"
                      placeholder="Select Specialist"
                      v-model="formData.specialist_id_to"
                      filterable
                      @change="getAppointmentsTo"
                    >
                      <el-option value="" label="Any Specialist" />
                      <el-option
                        v-for="specialist in allSpecialist"
                        :value="specialist.id"
                        :label="specialist?.full_name"
                        :key="specialist.id"
                      />
                    </el-select>
                  </el-form-item>
                  <el-form-item class="col-6 d-flex px-6" prop="to_date">
                    <el-date-picker
                      v-model="formData.to_date"
                      type="date"
                      placeholder="Pick a day"
                      :editable="false"
                      :clearable="false"
                      @change="getAppointmentsTo()"
                    />
                  </el-form-item>
                </div>
              </div>
              <div class="pt-5 row">
                <div class="col-6">
                  <template v-if="formData.from_date">
                    <div class="caption-content" v-if="aptListFrom.length > 0">
                      {{ aptListFrom.length + " " }} Appointment{{
                        aptListFrom.length > 1 ? "s " : " "
                      }}
                      found
                    </div>
                    <div
                      class="select-new-apt-caption"
                      v-else-if="aptListFrom.length === 0"
                    >
                      No Appointments found on selected date
                    </div>
                    <schedule-overview
                      :time-slots="selectedFromSpecialistTimeSlot"
                      :specialist-id="formData.specialist_id_from"
                    />
                  </template>
                </div>
                <div class="col-6">
                  <template v-if="formData.to_date">
                    <div v-if="aptListTo.length > 0">
                      {{ aptListTo.length + " " }} Appointment{{
                        aptListTo.length > 1 ? "s " : " "
                      }}
                      found
                    </div>
                    <div class="no-Apt" v-else-if="aptListTo.length === 0">
                      No Appointments found on selected date
                    </div>
                    <schedule-overview
                      :time-slots="selectedSpecialistTimeSlot"
                      :specialist-id="formData.specialist_id_to"
                    />
                  </template>
                </div>
              </div>
              <div class="row">
                <div class="px-6">
                  <button
                    class="btn btn-primary mt-3 w-100"
                    @click.prevent="handleSearch"
                  >
                    MOVE
                  </button>
                </div>
              </div>
            </el-form>
            <el-form
              :model="formData"
              ref="confirmFormRef"
              v-if="step === 1"
              v-loading="loading"
            >
              <div class="apt-description">
                <span class="me-1">Specialist:</span>
                <span class="caption-content me-2">
                  {{
                    allSpecialist.filter(
                      (c) => c.id === formData.specialist_id_from
                    )[0]
                      ? allSpecialist.filter(
                          (c) => c.id === formData.specialist_id_from
                        )[0].full_name
                      : "Any"
                  }}
                  <span class="text-muted"> to </span>
                  {{
                    allSpecialist.filter(
                      (c) => c.id === formData.specialist_id_to
                    )[0]
                      ? allSpecialist.filter(
                          (c) => c.id === formData.specialist_id_to
                        )[0].full_name
                      : "Any"
                  }}
                </span>
                <span class="me-1">Time Requirement:</span>
                <span class="caption-content me-2">
                  {{
                    formData.matchAppointmentTime
                      ? " Time matching"
                      : " Time not matching"
                  }}
                </span>
                <span class="me-1">Allow DoubleBooking:</span>
                <span class="caption-content me-2">
                  {{ formData.allowDoubleBooking ? " Yes" : " No" }}
                </span>
                <span class="me-1">Appointment Type Matching:</span>
                <span class="caption-content me-2">
                  {{ formData.matchAppointmentTime ? " Yes" : " No" }}
                </span>
              </div>
              <div class="row apt-wrapper">
                <div class="col-5">
                  <template
                    v-for="apt in updatedAppointments.oldApt"
                    :key="apt.id"
                  >
                    <div class="my-4 p-2 border border-secondary">
                      <div>Patient Name: {{ apt.patient_name.full }}</div>
                      <div>Specialist Name: {{ apt.specialist_name }}</div>
                      <div>Clinic Name: {{ apt.clinic.name }}</div>
                      <div>Date: {{ apt.date }}</div>
                      <div>
                        Time: {{ apt.start_time + " -" + apt.end_time }}
                      </div>
                      <div>Type: {{ apt.appointment_type_name }}</div>
                    </div>
                  </template>
                </div>
                <div
                  class="my-4 p-2 col-2 d-flex align-content-center fill-height align-items-center justify-content-center"
                >
                  <img
                    alt="Move Arrow"
                    src="media/logos/bulk-move-arrow.png"
                    class="move-arrow"
                  />
                </div>
                <div class="col-5">
                  <template
                    v-for="apt in updatedAppointments.newApt"
                    :key="apt.id"
                  >
                    <div class="my-4 p-2 border border-secondary">
                      <div>Patient Name: {{ apt.patient_name.full }}</div>
                      <div>Specialist Name: {{ apt.specialist_name }}</div>
                      <div>Clinic Name: {{ apt.clinic.name }}</div>
                      <div>Date: {{ apt.date }}</div>
                      <div>
                        Time: {{ apt.start_time + " -" + apt.end_time }}
                      </div>
                      <div>Type: {{ apt.appointment_type_name }}</div>
                    </div>
                  </template>
                </div>
              </div>
            </el-form>
          </div>
          <div
            class="row appointment-type d-flex justify-content-center mt-6"
            v-if="step === 1"
          >
            <div class="col-6 px-4 d-flex justify-content-center">
              <button
                class="btn btn-primary mt-3 w-25 mx-4"
                type="button"
                @click="handleMove"
              >
                CONFIRM
              </button>
              <button
                class="btn btn-warning mt-3 w-25 mx-4"
                @click="handleCancel"
                type="button"
              >
                CANCEL
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, ref, onMounted, reactive } from "vue";
import { Actions } from "@/store/enums/StoreEnums";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import moment from "moment";
import { ElMessage } from "element-plus";
import swal from "sweetalert2";
import ScheduleOverview from "@/components/appointments/partials/ScheduleOverview.vue";
import { ISpecialist } from "@/store/modules/SpecialistsModule";
import { Modal } from "bootstrap";

export default defineComponent({
  name: "bulk-move-apt-modal",
  components: { ScheduleOverview },
  props: {
    isDisableAptTypeList: { type: Boolean },
    action: { type: String },
  },
  setup(props) {
    const store = useStore();

    const bulkMoveAptModalRef = ref<HTMLElement | null>(null);
    const loading = ref(false);
    const aptData = computed(() => store.getters.getAptSelected);
    const aptTypelist = computed(() => store.getters.getAptTypesList);
    const cliniclist = computed(() => store.getters.clinicsList);
    const allSpecialist = computed(() => store.getters.getSpecialistList);
    const aptListFrom = computed(() => store.getters.getAptList);
    const aptListTo = computed(() => store.getters.getUserAptList);
    const organization = computed(() => store.getters.userOrganization);

    const step = ref<number>(0);
    const formData = ref({
      appointment_type_id: null,
      appointment_type_name: "",
      clinic_id: null,
      specialist_id_from: null,
      specialist_id_to: null,
      date: null,
      to_date: null,
      from_date: null,
      allowDoubleBooking: false,
      matchAppointmentRestrictions: false,
      matchAppointmentTime: false,
    });

    const formRef = ref();
    const confirmFormRef = ref();
    const rules = reactive({
      specialist_id_from: [
        {
          required: true,
          message: "Please select a specialist",
          trigger: "blur",
        },
      ],
      to_date: [
        {
          required: true,
          message: "Please select a date",
          trigger: "blur",
        },
      ],

      from_date: [
        {
          required: true,
          message: "Please select a date",
          trigger: "blur",
        },
      ],
      specialist_id_to: [
        {
          required: true,
          message: "Please select a specialist",
          trigger: "blur",
        },
      ],
    });

    interface updateApt {
      oldApt: Array<unknown>;
      newApt: Array<unknown>;
    }

    const selectedToSpecialist = ref<ISpecialist>();

    const updatedAppointments = ref<updateApt>({
      oldApt: [],
      newApt: [],
    });
    const appointmentRestriction = ref(["CONSULTATION", "NONE", "PROCEDURE"]);

    const handleChangeAppointmentType = () => {
      formData.value.appointment_type_name = aptTypelist.value.filter(
        (t) => t.id === formData.value.appointment_type_id
      )[0].name;
    };

    onMounted(() => {
      store.dispatch(Actions.SPECIALIST.LIST);
      store.dispatch(AppointmentActions.APPOINTMENT_TYPES.LIST);
      const bulkModal = document.getElementById(
        "modal_bulk_move_apt"
      ) as HTMLInputElement;

      bulkModal.addEventListener("hidden.bs.modal", function () {
        clearData();
      });
    });

    const handleSearch = async () => {
      if (!formRef.value) return;
      formRef.value.validate((valid) => {
        if (!valid) {
          return;
        }
      });

      if (aptListFrom.value.length <= 0) {
        ElMessage({
          message: "No Appointment found on selected date",
          grouping: true,
          type: "error",
        });
        return;
      }
      if (!checkDoctorWorkDays()) {
        ElMessage({
          message: "Selected specialist doesn't work on selected date",
          grouping: true,
          type: "error",
        });
        return;
      }

      await checkAppointmentsClinic();
      moveAppointments();
      if (updatedAppointments.value.newApt.length > 0) {
        step.value = 1;
      } else {
        warningPopUp(["Free space couldn't be found on selected date"]);
      }
    };

    const handleBack = () => {
      step.value = step.value - 1;
      clearData();
    };

    const handleMove = () => {
      loading.value = true;
      store
        .dispatch(AppointmentActions.APT.BULK.UPDATE, {
          id: 6,
          appointments: updatedAppointments.value.newApt,
        })
        .then(() => {
          ElMessage({
            message: "Appointments moved successfully!",
            grouping: false,
            type: "success",
          });
          loading.value = false;
          resetForm();
          getAppointmentsFrom();
          getAppointmentsTo();
          handleCancel();
        });
    };

    const getAppointmentsFrom = () => {
      if (!formData.value.from_date) return;
      loading.value = true;
      const fromDate = moment(formData.value.from_date).format("YYYY-MM-DD");
      store
        .dispatch(AppointmentActions.LIST, {
          date: fromDate,
          specialist_id: formData.value.specialist_id_from
            ? formData.value.specialist_id_from
            : null,
        })
        .then(() => {
          loading.value = false;
        });
    };

    const getAppointmentsTo = () => {
      if (!formData.value.to_date) return;
      loading.value = true;
      const toDate = moment(formData.value.to_date).format("YYYY-MM-DD");
      store
        .dispatch(AppointmentActions.APT.BULK.LIST, {
          date: toDate,
          specialist_id: formData.value.specialist_id_to
            ? formData.value.specialist_id_to
            : null,
        })
        .then(() => {
          loading.value = false;
        });
    };

    //Check selected specialist work on selected date or not
    const checkDoctorWorkDays = () => {
      const specialistId = formData.value.specialist_id_to;
      const toDate = moment(formData.value.to_date).format("ddd").toUpperCase();
      const specialist = allSpecialist.value.filter((spt) => {
        if (spt.id === specialistId) {
          const doc = spt.schedule_timeslots.filter((slot) => {
            if (slot.week_day == toDate) {
              return true;
            }
          })[0];
          return doc;
        }
      });
      if (specialist.length > 0) {
        selectedToSpecialist.value = specialist[0];
        return true;
      }
      selectedToSpecialist.value = undefined;
      return false;
    };

    // check clinics are same or not
    const checkAppointmentsClinic = async () => {
      let fromClinics: number[] = [];
      let toClinics: number[] = [];
      let warnings: string[] = [];
      const toDate: string = moment(formData.value.to_date)
        .format("ddd")
        .toUpperCase();

      aptListFrom.value.forEach((apt) => {
        if (!fromClinics.includes(apt.clinic_id))
          fromClinics.push(apt.clinic_id);
      });

      selectedToSpecialist.value?.schedule_timeslots.forEach((slot: any) => {
        if (slot.week_day === toDate) {
          if (!toClinics.includes(slot.clinic_id))
            toClinics.push(slot.clinic_id);
        }
      });

      if (fromClinics.length > 1) {
        warnings.push(
          moment(formData.value.from_date).format("YYYY-MM-DD") +
            " appointments have more than one clinic"
        );
      }
      if (toClinics.length > 1) {
        warnings.push(
          selectedToSpecialist.value?.full_name +
            " is working more than one clinic on " +
            moment(formData.value.to_date).format("YYYY-MM-DD")
        );
      }

      if (JSON.stringify(fromClinics) !== JSON.stringify(toClinics)) {
        warnings.push("Selected Date clinics aren't matching each other");
      }
      if (warnings.length > 0) await warningPopUp(warnings);
    };

    const selectedSpecialistTimeSlot = computed(() => {
      const toDate = moment(formData.value.to_date).format("ddd").toUpperCase();
      const specialist = allSpecialist.value.filter(
        (spt) => spt.id === formData.value.specialist_id_to
      )[0];
      if (!specialist) return [];
      return specialist.schedule_timeslots.filter(
        (slot) => slot.week_day == toDate
      );
    });

    const selectedFromSpecialistTimeSlot = computed(() => {
      const fromDate = moment(formData.value.from_date)
        .format("ddd")
        .toUpperCase();
      const specialist = allSpecialist.value.filter(
        (spt) => spt.id === formData.value.specialist_id_from
      )[0];
      if (!specialist) return [];
      return specialist.schedule_timeslots.filter(
        (slot) => slot.week_day == fromDate
      );
    });

    const moveAppointments = () => {
      updatedAppointments.value.oldApt = [];
      updatedAppointments.value.newApt = [];
      const restriction = appointmentRestriction.value;
      const isSameTime = formData.value.matchAppointmentTime;
      const tempDate = moment(formData.value.to_date).format("YYYY-MM-DD");
      let toApts = aptListTo.value.map((aptT) => {
        return { ...aptT };
      });

      aptListFrom.value.forEach((apt) => {
        if (formData.value.matchAppointmentRestrictions)
          updateRestrictions(apt);

        // check if we can timeslot first
        if (canAddAppointment(apt.start_time, apt.end_time, toApts)) {
          updateAppointment(
            apt,
            apt.start_time,
            apt.end_time,
            tempDate,
            formData.value.specialist_id_to
          );
          toApts.push(apt);
        } else {
          // if we can't get same time
          // get max start time we can go when there is more than one time slots
          let found = false;
          selectedSpecialistTimeSlot.value.forEach((slot) => {
            if (
              !found &&
              restriction.includes(slot.restriction) &&
              !isSameTime
            ) {
              const aptStartTime = moment(apt.date + "T" + apt.start_time);
              const aptEndTime = moment(apt.date + "T" + apt.end_time);

              const slotStartTime = moment(apt.date + "T" + slot.start_time);
              const slotEndTime = moment(apt.date + "T" + slot.end_time);

              const aptDuration = moment.duration(
                aptEndTime.diff(aptStartTime)
              );
              const shiftDuration = moment
                .duration(slotEndTime.diff(slotStartTime))
                .asMinutes();
              const minAptTime = organization.value.appointment_length;
              const loopValue = shiftDuration / minAptTime;

              let tempStartTime = slotStartTime.format("HH:mm:ss");
              let tempEndTime = slotStartTime
                .add(aptDuration, "minutes")
                .format("HH:mm:ss");
              for (let i = 1; i < loopValue; i++) {
                if (canAddAppointment(tempStartTime, tempEndTime, toApts)) {
                  updateAppointment(
                    apt,
                    tempStartTime,
                    tempEndTime,
                    tempDate,
                    formData.value.specialist_id_to
                  );
                  toApts.push(apt);
                  found = true;
                  break;
                } else {
                  tempStartTime = moment(apt.date + "T" + tempStartTime)
                    .add(minAptTime, "minutes")
                    .format("HH:mm:ss");
                  tempEndTime = moment(apt.date + "T" + tempEndTime)
                    .add(minAptTime, "minutes")
                    .format("HH:mm:ss");
                }
                //break and move to next apt if it is more than shift end time
                if (tempEndTime > slot.end_time) {
                  break;
                }
              }
            }
          });
        }
      });
    };

    const updateAppointment = (apt, startTime, endTime, date, specialistId) => {
      const specialist = allSpecialist.value.filter(
        (spt) => spt.id === specialistId
      )[0];

      let clinic = selectedSpecialistTimeSlot.value.filter(
        (slot) => slot.start_time <= startTime && slot.end_time >= endTime
      )[0].clinic;

      updatedAppointments.value.oldApt.push({ ...apt });
      apt.start_time = startTime;
      apt.arrival_time = moment(date + "T" + startTime)
        .subtract(30, "minutes")
        .format("HH:mm:ss");
      apt.end_time = endTime;
      apt.specialist_id = specialist.id;
      apt.specialist = specialist;
      apt.specialist_name = specialist.full_name;
      apt.clinic_id = clinic.id;
      apt.clinic = clinic;
      apt.date = date;
      updatedAppointments.value.newApt.push({ ...apt });
    };

    const canAddAppointment = (start_time, end_time, toApts) => {
      let result = -1;
      const restrictions = appointmentRestriction.value;
      selectedSpecialistTimeSlot.value.forEach((slot) => {
        if (
          restrictions.includes(slot.restriction) &&
          slot.start_time <= start_time &&
          slot.end_time >= end_time
        ) {
          result = 0;
          toApts.forEach((apt) => {
            if (apt.start_time < end_time && start_time < apt.end_time) {
              ++result;
            }
          });
        }
      });

      if (formData.value.allowDoubleBooking && result <= 1 && result > -1)
        return true;
      return result === 0;
    };

    const warningPopUp = (message) => {
      let html = "";
      html =
        "<h1>WARNING! </h1><div style='height: 160px; width: 100%; padding: 10px; color: grey; font-size: small; border: 1px solid #353333'> <ul>";

      message.forEach((error) => {
        html += "<li>" + error + "</li>";
      });
      html += "</ul></div>";

      hideModal(bulkMoveAptModalRef.value);
      return new Promise(function (resolve) {
        swal
          .fire({
            title: "Are you sure?",
            text: message,
            html: html,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm",
            cancelButtonText: "Cancel",
          })
          .then((result) => {
            const modal = new Modal(
              document.getElementById("modal_bulk_move_apt")
            );
            modal.show();
            if (result.value) {
              resolve(null);
            }
          });
      });
    };

    const updateRestrictions = (apt) => {
      if (apt.appointment_type_id) {
        const type = aptTypelist.value.filter(
          (typ) => typ.id === apt.appointment_type_id
        )[0].type;
        appointmentRestriction.value = [];
        appointmentRestriction.value.push(type);
        appointmentRestriction.value.push("NONE");
      }
    };

    const clearData = () => {
      step.value = 0;
      getAppointmentsFrom();
      getAppointmentsTo();
    };

    const handleCancel = () => {
      clearData();
      hideModal(bulkMoveAptModalRef.value);
    };

    const resetForm = () => {
      formData.value.specialist_id_from = null;
      formData.value.specialist_id_to = null;
      formData.value.to_date = null;
      formData.value.from_date = null;
    };

    return {
      props,
      aptData,
      aptTypelist,
      cliniclist,
      allSpecialist,
      formData,
      bulkMoveAptModalRef,
      loading,
      handleSearch,
      handleMove,
      moment,
      handleChangeAppointmentType,
      handleBack,
      getAppointmentsFrom,
      getAppointmentsTo,
      aptListFrom,
      aptListTo,
      rules,
      formRef,
      confirmFormRef,
      selectedSpecialistTimeSlot,
      selectedFromSpecialistTimeSlot,
      updatedAppointments,
      handleCancel,
      step,
      selectedToSpecialist,
    };
  },
});
</script>

<style lang="scss" scoped>
#modal_bulk_move_apt {
  .el-divider--horizontal {
    margin: 0px;
  }

  .appointment-type label {
    display: none;
  }

  .select-new-apt-caption {
    color: red;
    text-transform: uppercase;
    font-weight: bold;
  }

  .no-Apt {
    color: #309b41;
    text-transform: uppercase;
    font-weight: bold;
  }

  .apt-description {
    font-size: 16px;
    padding-bottom: 15px;
  }

  .caption-content {
    color: #3e7ba0;
    font-weight: 700;
  }

  .apt-card {
    border-style: solid;
    border-radius: 10px;
    padding: 20px;
    border-color: #3e7ba0;
    border-width: 2px;
    margin: 0 19px;
    width: 45%;
  }
}

.move-arrow {
  width: 70%;
}

.apt-wrapper {
  max-height: 500px;
}
</style>
