<template>
  <button
    :class="`justify-content-${justify} btn btn-light-${color} border border-${color} shadow p-2 px-4 d-flex align-items-center`"
    :data-kt-indicator="loading ? 'on' : null"
    :disabled="loading || disabled"
  >
    <span :class="`svg-icon svg-icon-${iconSize}x`">
      <inline-svg v-if="iconPath" :src="iconPath" />
    </span>

    <span class="d-block text-start">
      <span v-if="!loading" class="d-block indicator-label">
        {{ text }}
      </span>

      <span v-if="loading" class="d-block indicator-progress">
        {{ loadingText }}
        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
      </span>
    </span>
  </button>
</template>
<script lang="ts">
export default {
  props: {
    iconPath: { required: false, type: String },
    text: { required: false, type: String },
    color: { default: "primary", type: String },
    justify: { default: "center", type: String },
    iconSize: { default: "2", type: String },
    loadingText: { default: "Please wait...", type: String },
    loading: { default: false, type: Boolean },
    disabled: { default: false, type: Boolean },
  },
};
</script>
