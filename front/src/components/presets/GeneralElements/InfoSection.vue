<template>
  <a>
    <label class="fs-5 text-primary"
      >{{ heading }}: <span class="text-black fs-5"><slot /></span
    ></label>
  </a>
</template>
<script lang="ts">
export default {
  props: {
    heading: { required: true, type: String },
  },
};
</script>
