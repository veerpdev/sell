<template>
  <inline-svg :src="icons[icon]" />
</template>
<script lang="ts">
import icons from "@/core/data/icons";
export default {
  props: {
    icon: { type: String },
  },
  setup() {
    return {
      icons,
    };
  },
};
</script>
