<template>
  <div class="card w-75 mx-auto">
    <h1 class="m-auto p-6">The Human Resource Module is under construction.</h1>
  </div>
</template>

<script>
import { defineComponent, onMounted } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";

export default defineComponent({
  name: "hrm-dashboard",
  setup() {
    onMounted(() => {
      setCurrentPageBreadcrumbs("HRM", []);
    });
  },
});
</script>
