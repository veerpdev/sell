<template>
  <CardSection class="col-md-6 col-sm-12" heading="Medical History">
    <template #header-actions>
      <IconButton label="Create New" @click="handleAddMedicalHistory" />
    </template>
    <table class="table">
      <caption>
        Patient medical history table
      </caption>
      <tr class="fw-bold">
        <th>Timeframe</th>
        <th>Description</th>
        <th>Added by</th>
      </tr>
      <tr v-for="history in patient.medical_history" :key="history.id">
        <td>{{ history.timeframe }}</td>
        <td>{{ history.description }}</td>
        <td>{{ history.added_by }}</td>
      </tr>
    </table>
  </CardSection>
</template>
<script lang="ts">
export default {
  props: {
    patient: { required: true, type: Object },
  },
  setup() {
    const handleAddMedicalHistory = () => {
      alert("");
    };
    return {
      handleAddMedicalHistory,
    };
  },
};
</script>
