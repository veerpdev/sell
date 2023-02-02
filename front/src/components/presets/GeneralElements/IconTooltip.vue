<template>
  <div class="d-inline-block mb-2 p-2">
    <div class="d-flex">
      <span
        @mouseover="toggle = true"
        @mouseout="toggle = false"
        class="svg-icon-primary svg-icon svg-icon-2x btn m-0 p-0"
      >
        <InlineSVG :icon="icon" />
      </span>
    </div>
    <div
      v-if="toggle"
      :style="`
        background-color: none;
        z-index: 100;
        margin-left: ${xOffset}px !important;
      `"
      class="position-absolute mt-2"
    >
      <slot></slot>
    </div>
  </div>
</template>
<script lang="ts">
import { ref } from "vue";
export default {
  props: {
    icon: { required: true, type: String },
    xOffset: { required: false, type: Number },
  },
  setup() {
    const toggle = ref<boolean>(false);

    return { toggle };
  },
};
</script>
