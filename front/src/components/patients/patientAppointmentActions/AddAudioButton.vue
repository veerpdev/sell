<template>
  <LargeIconButton text="Add Audio" @click="handleAddAudio" />
  <CreateAudioModal :patientId="patient.id"></CreateAudioModal>
</template>

<script lang="ts">
import { Modal } from "bootstrap";

import CreateAudioModal from "@/views/patients/modals/CreateAudioModal.vue";

export default {
  components: { CreateAudioModal },
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
    const handleAddAudio = () => {
      const modal = new Modal(document.getElementById("modal_create_audio"));
      modal.show();
    };

    return { handleAddAudio };
  },
};
</script>
