<template>
  <a
    class="btn btn-light btn-icon-primary p-2 my-auto"
    :class="{ 'opacity-50 ': disabled }"
    :disabled="disabled"
  >
    <el-tooltip
      :disabled="tooltip == null"
      :content="tooltip"
      placement="bottom"
      effect="light"
    >
      <div class="d-flex gap-2" :class="{ 'mx-2': iconSRC && label }">
        <span v-if="iconSRC" class="svg-icon svg-icon-1 m-0">
          <inline-svg :src="iconSRC" />
        </span>
        <span v-if="label"> {{ label }} </span>
      </div>
    </el-tooltip>
  </a>
</template>

<script lang="ts">
export default {
  props: {
    label: { type: String, required: false },
    iconSRC: { type: String, required: false },
    disabled: { type: Boolean, default: false },
    tooltip: { type: String, default: null },
  },
};
</script>
