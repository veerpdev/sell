<template>
  <span
    class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
  >
    <span class="svg-icon svg-icon-4 me-1">
      <inline-svg :src="iconSRC" />
    </span>
    <slot></slot>
  </span>
</template>

<script lang="ts">
export default {
  props: {
    iconSRC: { type: String, required: true },
  },
};
</script>
