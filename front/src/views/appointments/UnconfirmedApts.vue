<template>
  <AppointmentList
    :params="{
      confirmation_status: 'PENDING',
    }"
    actionConfirmTitle="CONFIRM"
    :actionConfirm="handleConfirmAppointment"
    actionCancelTitle="CANCEL"
    :actionCancel="handleCancelAppointment"
    actionResendMessageTitle="Resend Message"
    :actionResendMessage="handleResendMessage"
  >
  </AppointmentList>
</template>

<script>
import { defineComponent, onMounted, ref } from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import AppointmentList from "@/views/appointments/AppointmentList.vue";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import Swal from "sweetalert2/dist/sweetalert2.min.js";
import moment from "moment";

export default defineComponent({
  name: "admin-main",
  components: { AppointmentList },
  setup() {
    const store = useStore();

    onMounted(() => {
      setCurrentPageBreadcrumbs("Unconfirmed Appointments", ["Booking"]);
    });

    const getData = (dateRange) => {
      let now = moment();
      let data = {};
      switch (dateRange) {
        case "Today": {
          data = { date: now.format() };
          break;
        }
        case "All": {
          data = {
            after_date: now.format(),
          };
          break;
        }
        case "Week": {
          data = {
            after_date: now.format(),
            before_date: now.endOf("week").format(),
          };
          break;
        }
        case "Month": {
          data = {
            after_date: now.format(),
            before_date: now.endOf("month").format(),
          };
          break;
        }
        case "Fortnight": {
          data = {
            after_date: now.format(),
            before_date: now.add(1, "week").endOf("week").format(),
          };
          break;
        }
        default:
          break;
      }
      return data;
    };

    const handleConfirmAppointment = async (appointmentId, dateRange) => {
      let dateRangeFilter = getData(dateRange);
      const html =
        "<h3>Are you sure you would like to confirm this appointment?</h3><br/>";
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
          await store
            .dispatch(AppointmentActions.CONFIRMATION_STATUS.UPDATE, {
              id: appointmentId,
              confirmed: true,
            })
            .then(() => {
              store.dispatch(AppointmentActions.LIST, {
                confirmation_status: "PENDING",
                ...dateRangeFilter,
              });
            });
        },
      });
    };

    const handleCancelAppointment = (appointmentId, dateRange) => {
      let dateRangeFilter = getData(dateRange);
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
              id: appointmentId,
              missed: missed,
              reason: data,
            })
            .then(() => {
              store.dispatch(AppointmentActions.LIST, {
                confirmation_status: "PENDING",
                ...dateRangeFilter,
              });
            });
        },
      });
    };

    const handleResendMessage = (appointmentId) => {
      store.dispatch(AppointmentActions.RESEND_MESSAGE, { id: appointmentId });
    };

    return {
      AppointmentList,
      handleConfirmAppointment,
      handleCancelAppointment,
      handleResendMessage,
    };
  },
});
</script>
