<template>
  <LargeIconButton text="Upload Document" @click="handleUploadDocument" />
  <UploadDocumentModal :patient="patient"></UploadDocumentModal>
</template>

<script lang="ts">
import { Modal } from "bootstrap";

import UploadDocumentModal from "@/views/patients/modals/UploadDocumentModal.vue";
import { PropType } from "vue";
import IPatient from "@/store/interfaces/IPatient";
export default {
  components: { UploadDocumentModal },
  props: {
    patient: {
      required: true,
      type: Object as PropType<IPatient>,
    },
  },
  setup() {
    const handleUploadDocument = () => {
      const modal = new Modal(document.getElementById("modal_upload_document"));
      modal.show();
    };

    return { handleUploadDocument };
  },
};
</script>
