<template>
  <div
    @click="openAlertModal"
    :class="
      'cursor-pointer alert bg-light-' +
      color +
      ' border border-' +
      color +
      ' d-flex flex-column flex-sm-row w-100 p-2 mb-6 '
    "
  >
    <span v-if="icon" class="'svg-icon svg-icon-2hx svg-icon-' + color">
      <inline-svg :src="icon" />
    </span>
    <h5 class="mx-2 p-2 my-auto fw-bold text-uppercase">{{ alert.title }}</h5>
  </div>
</template>
<script lang="ts">
import { onMounted, ref, PropType } from "vue";
import { Modal } from "bootstrap";
import IPatientAlert from "@/store/interfaces/IPatientAlert";

export default {
  props: {
    alert: { required: true, type: Object as PropType<IPatientAlert> },
  },
  setup(props) {
    const icon = ref<string>();
    const color = ref<string>();

    onMounted(() => {
      if (props.alert.alert_level === "WARNING") {
        color.value = "warning";
      } else if (props.alert.alert_level === "BLACKLISTED") {
        color.value = "danger";
      } else {
        color.value = "primary";
      }
    });

    const openAlertModal = () => {
      const modal = new Modal(
        document.getElementById("modal_patient_alert_" + props.alert.id)
      );
      modal.show();
    };

    return {
      icon,
      color,
      openAlertModal,
    };
  },
};
</script>
