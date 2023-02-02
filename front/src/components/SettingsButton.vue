<template>
  <LargeIconButton
    class="mb-6"
    :iconPath="icons.left"
    text="Back To Settings"
    @click="backToSettings"
  />
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { useRouter } from "vue-router";
import icons from "@/core/data/icons";
export default defineComponent({
  setup() {
    const router = useRouter();
    const backToSettings = () => {
      router.push({ name: "manager-settings" });
    };
    return {
      backToSettings,
      icons,
    };
  },
});
</script>
