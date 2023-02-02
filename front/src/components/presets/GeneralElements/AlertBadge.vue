<template>
  <div
    :class="
      'alert bg-light-' +
      color +
      ' border border-' +
      color +
      ' d-flex flex-column flex-sm-row w-100 p-2 mb-6'
    "
  >
    <span :class="'svg-icon svg-icon-2hx svg-icon-' + color">
      <inline-svg :src="iconPath" />
    </span>
    <h5 class="mx-2 my-auto fw-light">{{ text }}</h5>
  </div>
</template>
<script lang="ts">
export default {
  props: {
    iconPath: { required: true, type: String },
    text: { required: true, type: String },
    color: { default: "primary", type: String },
  },
};
</script>
