<template>
  <div class="card mb-6">
    <div v-if="heading" class="card-header">
      <div class="card-title m-0">
        <HeadingText v-if="heading" :text="heading" />
      </div>
      <span class="my-auto">
        <slot name="header-actions"></slot>
      </span>
    </div>
    <div class="card-body">
      <slot></slot>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  props: {
    heading: { type: String, required: false },
  },
};
</script>
