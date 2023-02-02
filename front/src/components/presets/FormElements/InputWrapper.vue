<template>
  <div class="px-6">
    <el-tooltip :disabled="tooltip == null" placement="top" effect="dark">
      <template #content><span v-html="tooltip"></span></template>
      <span>
        <label
          v-if="label"
          class="fs-6 fw-bold mb-2"
          style="color: grey"
          :class="{ required: required }"
          >{{ label }}
        </label></span
      >
    </el-tooltip>
    <el-form-item :prop="prop" :rules="rule">
      <slot></slot>
    </el-form-item>
  </div>
</template>

<script lang="ts">
export default {
  props: {
    label: { type: String },
    prop: { type: String },
    rule: { type: Array, required: false },
    tooltip: { type: String, required: false },
    required: { type: Boolean, default: false },
  },
};
</script>
