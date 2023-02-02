<template>
  <AppointmentList
    :params="{
      procedure_approved_Status: 'NOT_APPROVED',
    }"
  ></AppointmentList>
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
      setCurrentPageBreadcrumbs("Unconfirmed Appointments", ["Booking"]);
    });

    return {
      AppointmentList,
    };
  },
});
</script>
