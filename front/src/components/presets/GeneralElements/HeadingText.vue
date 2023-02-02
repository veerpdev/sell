<template>
  <h3 class="fw-bold d-flex align-items-center text-dark">{{ text }}</h3>
</template>

<script lang="ts">
export default {
  props: {
    text: { type: String, required: true },
  },
};
</script>
