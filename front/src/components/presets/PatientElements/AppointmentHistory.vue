<template>
  <div class="row mt-4">
    <div class="col">
      <span class="fw-bold">Upcoming Appointments</span>
      <el-divider class="m-1" />
      <span v-for="appointment in futureAppointments" :key="appointment.id">
        {{ appointment.aus_formatted_date }}
        {{ appointment.formatted_appointment_time }}<br />
        {{ appointment.specialist_name }}<br />
        {{ appointment.appointment_type.name }}<br />
        <el-divider class="m-1" />
      </span>
    </div>
    <div class="col">
      <span class="fw-bold mb-2">Previous 5 Appointments</span>
      <el-divider class="m-1" />
      <span v-for="appointment in pastAppointments" :key="appointment.id">
        {{ appointment.aus_formatted_date }}
        {{ appointment.formatted_appointment_time }}<br />
        {{ appointment.specialist_name }}<br />
        {{ appointment.appointment_type.name }}<br />
        <el-divider class="m-1" />
      </span>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  props: {
    pastAppointments: { type: Array, required: true },
    futureAppointments: { type: Array, required: true },
  },
};
</script>
