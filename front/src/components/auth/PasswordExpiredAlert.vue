<template>
  <div class="card mb-5">
    <div class="card-body text-warning d-flex flex-row align-items-center">
      Your password has expired.
      <router-link
        class="nav-link text-active-primary me-6"
        to="/profile/password-change"
        active-class="active"
      >
        <el-button class="mx-2" link type="" size="small"
          >Click here to reset it now.</el-button
        >
      </router-link>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "password-expired-alert",
});
</script>
