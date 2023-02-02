<template>
  <AppointmentList
    :params="{
      is_wait_listed: '1',
    }"
    actionConfirmTitle="REMOVE FROM WAIT LIST"
    :actionConfirm="handleRemoveFromWaitList"
  >
  </AppointmentList>
</template>

<script>
import { defineComponent, onMounted } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import AppointmentList from "@/views/appointments/AppointmentList.vue";
export default defineComponent({
  name: "admin-main",
  components: { AppointmentList },
  setup() {
    onMounted(() => {
      setCurrentPageBreadcrumbs("Waitlisted Appointments", ["Booking"]);
    });

    const handleRemoveFromWaitList = (appointmentId) => {
      console.log(appointmentId);
    };

    return {
      AppointmentList,
      handleRemoveFromWaitList,
    };
  },
});
</script>
