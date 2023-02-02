<template>
  <LargeIconButton
    text="Update Collecting Person"
    @click="handleCollectingPerson"
  />

  <CollectingPersonModal :appointment="appointment" />
</template>

<script lang="ts">
import { PropType } from "vue";
import { Modal } from "bootstrap";
import CollectingPersonModal from "@/views/patients/modals/CollectingPersonModal.vue";
import IAppointment from "@/store/interfaces/IAppointment";

export default {
  components: { CollectingPersonModal },
  props: {
    appointment: {
      required: true,
      type: Object as PropType<IAppointment>,
    },
  },
  setup() {
    const handleCollectingPerson = () => {
      const modal = new Modal(
        document.getElementById("modal_collecting_person")
      );
      modal.show();
    };
    return { handleCollectingPerson };
  },
};
</script>
