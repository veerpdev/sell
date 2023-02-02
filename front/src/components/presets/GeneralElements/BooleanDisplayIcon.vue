<template>
  <InlineSVG
    v-if="boolean"
    :class="colored ? 'text-success' : ''"
    icon="tick"
  />
  <InlineSVG v-else :class="colored ? 'text-danger' : ''" icon="cross" />
</template>

<script lang="ts">
import InlineSVG from "./InlineSVG.vue";

export default {
  props: {
    boolean: { type: Boolean, required: true },
    colored: { type: Boolean, default: true },
  },
  components: { InlineSVG },
};
</script>
