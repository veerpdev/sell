<template>
  <LargeIconButton text="Pre-Admission Form" @click="handlePreAdmission()" />
  <ProcedureApprovalModal :selectedApt="appointment" />
</template>

<script lang="ts">
import { Modal } from "bootstrap";
import ProcedureApprovalModal from "@/views/patients/modals/ProcedureApprovalModal.vue";

export default {
  components: { ProcedureApprovalModal },
  props: {
    appointment: {
      required: true,
      type: Object,
    },
  },
  setup() {
    const handlePreAdmission = () => {
      const modal = new Modal(
        document.getElementById("modal_view_pre_admission")
      );
      modal.show();
    };
    return { handlePreAdmission };
  },
};
</script>
