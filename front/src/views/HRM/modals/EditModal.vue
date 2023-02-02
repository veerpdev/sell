<template>
  <div
    class="modal fade"
    id="hrm_modal_edit_schedule"
    tabindex="-1"
    aria-hidden="true"
    ref="editScheduleModalRef"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-1000px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_edit_schedule_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">{{ schedule._title }}</h2>
          <!--end::Modal title-->

          <!--begin::Close-->
          <div
            id="kt_modal_edit_schedule_close"
            data-bs-dismiss="modal"
            class="btn btn-icon btn-sm btn-active-icon-primary"
          >
            <span class="svg-icon svg-icon-1">
              <InlineSVG icon="cross" />
            </span>
          </div>
          <!--end::Close-->
        </div>
        <!--end::Modal header-->
        <!--begin::Form-->
        <el-form
          @submit.prevent="submit()"
          :model="formData"
          :rules="rules"
          ref="formRef"
        >
          <!--begin::Modal body-->
          <div class="modal-body py-10 px-lg-17">
            <!--begin::Scroll-->
            <div
              class="scroll-y me-n7 pe-7"
              id="kt_modal_edit_schedule_scroll"
              data-kt-scroll="true"
              data-kt-scroll-activate="{default: false, lg: true}"
              data-kt-scroll-max-height="auto"
              data-kt-scroll-dependencies="#kt_modal_edit_schedule_header"
              data-kt-scroll-wrappers="#kt_modal_edit_schedule_scroll"
              data-kt-scroll-offset="300px"
            >
              <div class="row" v-if="schedule._action == 'edit_employee_type'">
                <InputWrapper class="col-12" label="Type" prop="type">
                  <el-select class="w-100" v-model="formData.type" filterable>
                    <el-option
                      v-for="item in employeeTypes"
                      :value="item.value"
                      :label="item.label"
                      :key="item.value"
                    />
                  </el-select>
                </InputWrapper>
                <InputWrapper class="col-12" label="Role" prop="role" required>
                  <el-select v-model="formData.role_id" class="w-100">
                    <el-option
                      v-for="item in employeeRoles.filter((e) => e.value > 2)"
                      :value="item.value"
                      :label="item.label"
                      :key="item.value"
                    />
                  </el-select>
                </InputWrapper>
                <InputWrapper
                  class="col-12"
                  label="Employee"
                  prop="user"
                  filterable
                >
                  <el-select
                    class="w-100"
                    v-model="formData.user_id"
                    filterable
                  >
                    <el-option
                      v-for="item in employeeList"
                      :value="item.id"
                      :label="item.full_name"
                      :key="item.id"
                    />
                  </el-select>
                </InputWrapper>
              </div>

              <div v-if="schedule._action == 'edit_weekly_time'">
                <div v-for="(day, index) in formData.timeslots" :key="index">
                  <div
                    class="m-3 time-slots-box d-flex flex-row time-slots-wrapper"
                  >
                    <div class="card d-flex flex-row time-slots-view">
                      <InputWrapper
                        class="col px-1"
                        label="Start Time"
                        :prop="'timeslot-' + index"
                      >
                        <el-time-select
                          placeholder="Start time"
                          :max-time="day?.end_time"
                          class="w-100"
                          start="07:00"
                          step="00:15"
                          end="18:30"
                          format="HH:mm"
                          :editable="false"
                          v-model="day.start_time"
                          :prop="'starttime-' + index"
                          @change="verifyAnesthetist(day)"
                        />
                      </InputWrapper>
                      <div class="gap">:</div>
                      <InputWrapper
                        class="px-1 col"
                        label="End Time"
                        :prop="'timeslot-' + index"
                      >
                        <el-time-select
                          class="w-100"
                          placeholder="End time"
                          :min-time="day?.start_time"
                          start="07:00"
                          step="00:15"
                          end="18:30"
                          format="HH:mm"
                          :editable="false"
                          v-model="day.end_time"
                          :prop="'endtime-' + index"
                          @change="verifyAnesthetist(day)"
                        />
                      </InputWrapper>
                      <InputWrapper
                        class="col"
                        label="Type"
                        :prop="'category-' + index"
                      >
                        <el-select
                          class="w-100"
                          type="text"
                          v-model="day.category"
                          :prop="'category-select-' + index"
                        >
                          <el-option
                            v-for="item in schCategories"
                            :value="item"
                            :label="item"
                            :key="item"
                          />
                        </el-select>
                      </InputWrapper>
                      <InputWrapper
                        class="col"
                        label="Clinic"
                        :prop="'restriction-' + index"
                      >
                        <el-select
                          class="w-100"
                          type="text"
                          v-model="day.clinic_id"
                          :prop="'restriction-select-' + index"
                          @visible-change="verifyAnesthetist(day)"
                        >
                          <el-option
                            v-for="item in clinics"
                            :value="item.id"
                            :label="item.name"
                            :key="item.id"
                          />
                        </el-select>
                      </InputWrapper>
                      <InputWrapper
                        class="col"
                        label="Restriction"
                        :prop="'restriction-' + index"
                        v-if="formData.role_id == 5"
                      >
                        <el-select
                          class="w-100"
                          type="text"
                          v-model="day.restriction"
                          :prop="'restriction-select-' + index"
                          @change="verifyAnesthetist(day)"
                        >
                          <el-option
                            v-for="item in restrictionsTypes"
                            :value="item"
                            :label="item"
                            :key="item"
                          />
                        </el-select>
                      </InputWrapper>
                      <InputWrapper
                        class="col"
                        label="Anesthetist"
                        :prop="'restriction-' + index"
                        v-if="
                          day.restriction !== 'CONSULTATION' &&
                          formData.role_id == 5
                        "
                      >
                        <el-select
                          class="w-100"
                          type="text"
                          v-model="day.anesthetist_id"
                          @visible-change="verifyAnesthetist(day)"
                          :prop="'restriction-select-' + index"
                        >
                          <el-option
                            v-for="anesthetist in anesthetists"
                            :value="anesthetist.id"
                            :label="anesthetist.first_name"
                            :key="anesthetist.id"
                            :disabled="anesthetist.isDisabled"
                          />
                        </el-select>
                      </InputWrapper>
                      <InputWrapper
                        class="col"
                        label="Status"
                        :prop="'status-' + index"
                        v-if="!day.id ? true : false"
                      >
                        <el-select
                          v-model="day.status"
                          class="w-100"
                          label="Status"
                          placeholder="Select"
                          :disabled="day.id ? true : false"
                        >
                          <el-option
                            v-for="item in shiftStatusOptions"
                            :key="item.id"
                            :label="item.label"
                            :value="item.value"
                          />
                        </el-select>
                      </InputWrapper>
                    </div>
                    <div class="time-slots-delete">
                      <a
                        @click="handleDeleteTimeslot(index)"
                        class="btn btn-sm btn-icon btn-light"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Delete"
                      >
                        <!--begin::Svg Icon | path: icons/duotune/general/gen027.svg-->
                        <span class="svg-icon svg-icon-2">
                          <inline-svg
                            src="media/icons/duotune/general/gen034.svg"
                          />
                        </span>
                        <!--end::Svg Icon-->
                      </a>
                    </div>
                  </div>
                  <el-divider class="time-slots-divider" />
                </div>
                <div
                  class="border border-dashed border-primary m-3 time-slots-add-box d-flex flex-row"
                  @click="handleAddTimeslot()"
                >
                  ADD TIMESLOT
                </div>
              </div>
            </div>
            <!--end::Scroll-->
          </div>
          <!--end::Modal body-->

          <!--begin::Modal footer-->
          <div class="modal-footer flex-center">
            <!--begin::Button-->
            <button
              type="button"
              data-bs-dismiss="modal"
              id="kt_modal_edit_schedule_cancel"
              class="btn btn-light me-3"
              @click="clearData"
            >
              Cancel
            </button>
            <!--end::Button-->

            <!--begin::Button-->
            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-lg btn-primary"
              type="submit"
            >
              <span v-if="!loading" class="indicator-label">
                Update
                <span class="svg-icon svg-icon-3 ms-2 me-0">
                  <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
                </span>
              </span>
              <span v-if="loading" class="indicator-progress">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>
            <!--end::Button-->
          </div>
          <!--end::Modal footer-->
        </el-form>
        <!--end::Form-->
      </div>
    </div>
  </div>
</template>
<style lang="scss">
.time-slots-divider {
  margin-top: 0px !important;
}

.time-slots-box {
  margin-left: 0px !important;
  margin-right: 0px !important;

  .time-slots-view {
    & > div {
      padding-right: 0.5rem !important;
      padding-left: 0.5rem !important;
    }

    & > div.gap {
      align-items: center;
      display: flex;
      padding-right: 0px !important;
      padding-left: 0px !important;
    }
  }

  .time-slots-delete {
    display: flex;
    align-items: center;
    margin-right: 0.5rem;
  }
}

.time-slots-add-box {
  justify-content: center;
  cursor: pointer;
  padding: 1.5rem;
}
</style>
<script>
import { defineComponent, computed, ref, watch, onMounted } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import { Actions } from "@/store/enums/StoreEnums";
import employeeTypes from "@/core/data/employee-schedule-types";
import employeeRoles from "@/core/data/employee-roles";
import { HRMActions } from "@/store/enums/StoreHRMEnums";
import restrictionsTypes from "@/core/data/apt-restriction";
import schCategories from "@/core/data/schedule-category";
import { ElMessage } from "element-plus";
import InputWrapper from "@/components/presets/FormElements/InputWrapper";
import Swal from "sweetalert2";

export default defineComponent({
  name: "edit-admin-modal",
  components: { InputWrapper },
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const editScheduleModalRef = ref(null);
    const loading = ref(false);
    const deleteTimeslots = ref([]);
    const notifyUsers = ref([]);
    const shiftStatusOptions = ref([
      {
        value: "PUBLISHED",
        label: "PUBLISHED",
        id: 0,
      },
      {
        value: "UNPUBLISHED",
        label: "UNPUBLISHED",
        id: 1,
      },
    ]);
    const formData = ref({
      id: -1,
      clinic_id: -1,
      role_id: null,
      type: "PERMANENT",
      user_id: null,
      timeslots: [],
      deleteTimeslots: [],
      notifyUsers: [],
      organization_id: null,
      is_template: 1,
    });
    const rules = ref({
      role_id: [
        {
          required: true,
          message: "Role cannot be blank",
          trigger: "change",
        },
      ],
    });
    const orginalTimeslots = ref([]);

    const employeeList = computed(() => store.getters.hrmScheduleList);
    const schedule = computed(() => store.getters.hrmScheduleSelected);
    const timeslots = computed(() => store.getters.hrmSelectedScheduleList);
    // need to get anesthetist based on their weekly template
    const anesthetists = computed(() => store.getters.hrmAnesthetist);
    const clinics = computed(() => store.getters.clinicsList);
    watch([schedule, timeslots], () => {
      formData.value.id = schedule.value.id;
      formData.value.clinic_id = schedule.value.clinic_id;
      formData.value.role_id = schedule.value.role_id;
      formData.value.user_id = schedule.value.id;
      formData.value.organization_id = schedule.value.organization_id;
      orginalTimeslots.value = [];
      timeslots.value.map((slot) => {
        orginalTimeslots.value.push({ ...slot });
      });
      formData.value.timeslots = timeslots.value;
    });

    const handleAddTimeslot = () => {
      formData.value.timeslots.push({
        start_time: null,
        end_time: null,
        category: null,
        restriction: "NONE",
        anesthetist_id: null,
        clinic_id: null,
        hrm_weekly_schedule_template_id: schedule.value.id,
        week_day: schedule.value._day,
        date: schedule.value.dateOptions.date,
      });
    };

    const handleDeleteTimeslot = (index) => {
      if (formData.value.timeslots[index].id) {
        const { value: fruit } = Swal.fire({
          title: "Select a reason for Delete Shift",
          input: "select",
          width: 600,
          inputOptions: {
            AnnualLeave: "Annual Leave",
            SickLeave: "Sick Leave",
            UnpaidLeave: "Unpaid Leave",
            Vacation: "Vacation",
            Other: "Other",
          },
          inputPlaceholder: "Select",
          showCancelButton: true,
          inputValidator: (value) => {
            return new Promise((resolve) => {
              if (value === "") {
                resolve("Please select a reason to delete shift");
              } else {
                deleteTimeslots.value.push({
                  hrm_weekly_schedule_id: formData.value.timeslots[index].id,
                  reason: value,
                });
                notifyUsers.value.push(formData.value.timeslots[index].user_id);
                formData.value.timeslots = formData.value.timeslots.filter(
                  (t, i) => i != index
                );
                resolve();
              }
            });
          },
        });

        if (fruit) {
          Swal.fire(`You selected: ${fruit}`);
        }
      } else {
        formData.value.timeslots = formData.value.timeslots.filter(
          (t, i) => i != index
        );
      }
    };

    const confirmEditPublishedShifts = async () => {
      let isShiftEdited = false;
      let isShiftDeleted = false;
      orginalTimeslots.value.map((oSlot) => {
        formData.value.timeslots.map((slot) => {
          if (slot.id === oSlot.id && slot.status === "PUBLISHED") {
            if (JSON.stringify(slot) !== JSON.stringify(oSlot)) {
              isShiftEdited = true;
              notifyUsers.value.push(slot.user_id);
            }
          }
        });
        if (oSlot.status === "PUBLISHED") {
          const found = formData.value.timeslots.find(
            ({ id }) => id == oSlot.id
          );
          if (!found) isShiftDeleted = true;
        }
      });

      if (isShiftDeleted || isShiftEdited) {
        Swal.fire({
          title: "Are you sure?",
          text: "Are you sure you you want to make changes to a published shift",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Confirm",
        }).then((result) => {
          if (result.isConfirmed) {
            submitRequest();
          } else {
            notifyUsers.value = [];
          }
        });
      } else {
        submitRequest();
      }
    };

    const validateFormData = (formData) => {
      let result = true;
      if (
        formData.value.timeslots.length === 0 &&
        deleteTimeslots.value.length <= 0
      ) {
        ElMessage.error("Please add timeslot first.");
        return false;
      }
      formData.value.timeslots.map((t) => {
        if (t.start_time == null || t.start_time === "") {
          ElMessage.error("Start time cannot be blank");
          result = false;
        } else if (t.end_time == null || t.end_time === "") {
          ElMessage.error("End time cannot be blank");
          result = false;
        } else if (t.clinic_id == null || t.clinic_id === "") {
          ElMessage.error("Please select a clinic");
          result = false;
        } else if (t.category == null || t.category === "") {
          ElMessage.error("Please select a category");
          result = false;
        } else if (!t.id) {
          if (t.status == null || t.status === "") {
            ElMessage.error("Please select a status");
            result = false;
          }
        }
      });
      return result;
    };

    const submit = async () => {
      if (!formRef.value) {
        return;
      }
      if (!validateFormData(formData)) return;

      //validate overlap time range
      let overlap = formData.value.timeslots.filter((t, i) => {
        let res = formData.value.timeslots.filter((tt, ii) => {
          t.start_time += t.start_time.split(":").length < 3 ? ":00" : "";
          t.end_time += t.end_time.split(":").length < 3 ? ":00" : "";
          tt.start_time += tt.start_time.split(":").length < 3 ? ":00" : "";
          tt.end_time += tt.end_time.split(":").length < 3 ? ":00" : "";
          return (
            i !== ii &&
            ((tt.start_time > t.start_time && tt.start_time < t.end_time) ||
              (t.start_time > tt.start_time && t.start_time < tt.end_time) ||
              (tt.end_time > t.start_time && tt.end_time < t.end_time) ||
              (t.end_time > tt.start_time && t.end_time < tt.end_time))
          );
        });
        if (res?.length) return true;
        return false;
      });
      if (overlap?.length) {
        ElMessage.error("There is the over lap slots!");
        return;
      }
      confirmEditPublishedShifts();
    };

    const submitRequest = () => {
      formData.value.date = schedule.value.dateOptions.date;
      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          let _timeslots = [];
          formData.value.timeslots.map((t) => {
            if (
              t.category != null &&
              t.restriction != null &&
              t.start_time != null &&
              t.end_time != null
            ) {
              if (t.user_id == null) {
                t.organization_id = formData.value.organization_id;
                t.user_id = formData.value.user_id;
                t.is_template = 1;
              }
              _timeslots.push(t);
            }
          });
          schedule.value.hrm_weekly_schedule.map((t) => {
            if (t.week_day != schedule.value._day) {
              _timeslots.push(t);
            }
          });

          // Add deleted timeslots to payload
          if (deleteTimeslots.value.length > 0) {
            formData.value.deleteTimeslots = [];
            deleteTimeslots.value.map((slotId) => {
              formData.value.deleteTimeslots.push(slotId);
            });
          }
          // Add notify users to payload
          formData.value.notifyUsers = [];
          notifyUsers.value.map((userId) => {
            formData.value.notifyUsers.push(userId);
          });
          hideModal(editScheduleModalRef.value);
          formData.value.timeslots = _timeslots;
          store
            .dispatch(schedule.value._submit, formData.value)
            .then(() => {
              loading.value = false;
              store.dispatch(HRMActions.WEEKLY_SCHEDULE.LIST, {
                date: schedule.value.dateOptions.date,
              });
            })
            .catch((response) => {
              loading.value = false;
              console.log(response);
            });
        } else {
          // this.context.commit(Mutations.PURGE_AUTH);
        }
      });
    };

    const verifyAnesthetist = (data) => {
      let filteredAnesthetists = [];
      if (data.restriction !== "CONSULTATION") {
        anesthetists.value.forEach((anesthetist) => {
          anesthetist.isDisabled = true;
          anesthetist.hrm_weekly_schedule.map((slot) => {
            if (
              slot.clinic_id == data.clinic_id &&
              !filteredAnesthetists.includes(anesthetist) &&
              slot.start_time <= data.start_time &&
              slot.end_time >= data.end_time
            ) {
              let bookedSlots = existingAnesthetistBookings(
                data.week_day,
                anesthetist.id,
                data.id
              );
              // if there are any existing bookings check bookings are clashing or not
              if (bookedSlots.length > 0) {
                let result = 0;
                bookedSlots.forEach((bookedSlot) => {
                  if (
                    bookedSlot.startTime <= data.start_time + ":00" &&
                    bookedSlot.endTime > data.start_time + ":00"
                  ) {
                    result = result + 1;
                  } else if (
                    bookedSlot.startTime < data.end_time + ":00" &&
                    bookedSlot.endTime >= data.end_time + ":00"
                  ) {
                    result = result + 1;
                  } else if (
                    bookedSlot.startTime >= data.start_time + ":00" &&
                    bookedSlot.endTime <= data.end_time + ":00"
                  ) {
                    result = result + 1;
                  }
                });
                if (result === 0) {
                  filteredAnesthetists.push(anesthetist);
                  anesthetist.isDisabled = false;
                }
              } else {
                filteredAnesthetists.push(anesthetist);
                anesthetist.isDisabled = false;
              }
            }
          });
        });
      } else {
        data.anesthetist_id = null;
      }
      // verify selected anesthetist is in filtered anesthetists
      let isFound = false;
      filteredAnesthetists.map((anesthetist) => {
        if (anesthetist.id === data.anesthetist_id) {
          isFound = true;
          return;
        }
      });
      data.anesthetist_id = isFound ? data.anesthetist_id : null;
    };
    // che
    const existingAnesthetistBookings = (day, anesthetistId, slotId) => {
      let anesthetistBookedTimeSlots = [];
      employeeList.value.forEach((specialist) => {
        if (specialist.role_id === 5) {
          specialist.hrm_weekly_schedule.forEach((slot) => {
            if (slot.week_day === day && slot.id !== slotId) {
              if (slot.anesthetist_id === anesthetistId) {
                anesthetistBookedTimeSlots.push({
                  startTime: slot.start_time,
                  endTime: slot.end_time,
                });
              }
            }
          });
        }
      });
      return anesthetistBookedTimeSlots;
    };

    const clearData = () => {
      deleteTimeslots.value = [];
      notifyUsers.value = [];
    };
    onMounted(() => {
      store.dispatch(Actions.EMPLOYEE.LIST);
      const myModalEl = document.getElementById("hrm_modal_edit_schedule");
      myModalEl.addEventListener("hide.bs.modal", () => {
        clearData();
      });
    });
    return {
      formData,
      rules,
      submit,
      formRef,
      loading,
      editScheduleModalRef,
      employeeList,
      anesthetists,
      employeeTypes,
      employeeRoles,
      schedule,
      restrictionsTypes,
      schCategories,
      handleAddTimeslot,
      handleDeleteTimeslot,
      verifyAnesthetist,
      clinics,
      shiftStatusOptions,
      clearData,
    };
  },
});
</script>
<style scoped>
.dialog-footer button:first-child {
  margin-right: 10px;
}
</style>
