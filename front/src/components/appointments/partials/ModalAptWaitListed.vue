<template>
  <div class="row d-flex flex-nowrap w-75 ps-4">
    <span class="w-75 p-0"> Appointment wait listed : </span>
    <el-switch
      v-model="isWaitListed"
      class="ml-2 p-0"
      :width="40"
      inline-prompt
      active-text="Yes"
      inactive-text="No"
      :active-value="1"
      :inactive-value="0"
      @change="$emit('update', isWaitListed)"
    />
  </div>
</template>
<script lang="ts">
import { defineComponent, ref, watchEffect } from "vue";

export default defineComponent({
  components: {},
  props: {
    waitListed: {
      type: Number,
      required: true,
    },
  },

  emits: ["update"],

  setup(props) {
    const isWaitListed = ref<number>(0);

    watchEffect(() => {
      isWaitListed.value = props.waitListed;
    });
    return {
      isWaitListed,
    };
  },
});
</script>
