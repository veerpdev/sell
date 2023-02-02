<template>
  <LargeIconButton
    text="Create Report"
    @click="handleReport()"
    :loading="loading"
  />
</template>

<script lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
export default {
  props: {
    appointment: {
      required: true,
      type: Object,
    },
    patient: {
      required: true,
      type: Object,
    },
  },
  setup(props) {
    const router = useRouter();
    const loading = ref<boolean>(false);

    const handleReport = () => {
      loading.value = true;

      // router
      //   .push({
      //     name: "patients-document",
      //     params: {
      //       patientId: props.patient.id,
      //       appointmentId: props.appointment.id,
      //     },
      //   })
      //   .finally(() => {
      //     loading.value = false;
      //   });
    };

    return {
      handleReport,
      loading,
    };
  },
};
</script>
