<template>
  <LargeIconButton text="Add Recall" @click="handleAddRecall" />
  <RecallReminderModal></RecallReminderModal>
</template>

<script lang="ts">
import { Modal } from "bootstrap";

import RecallReminderModal from "@/views/patients/modals/RecallReminderModal.vue";
export default {
  components: { RecallReminderModal },
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
  setup() {
    const handleAddRecall = () => {
      const modal = new Modal(
        document.getElementById("modal_patient_recall_reminder")
      );
      modal.show();
    };

    return { handleAddRecall };
  },
};
</script>
