<template>
  <div
    v-if="!userData.signature && userData.role == 'specialist'"
    :class="'x-3 alert justify-content-center bg-light-warning border border-warning d-flex flex-column flex-sm-row w-100 p-2 mb-6'"
  >
    <span :class="'svg-icon svg-icon-2hx svg-icon-warning'">
      <InlineSVG icon="bell" />
    </span>
    <h5 class="mx-2 my-auto fw-light">
      WARNING: You don't have a signature set. Any correspondence sent will not
      have a signature attached
    </h5>
    <router-link
      class="nav-link text-active-primary me-6"
      to="/profile/signature"
      active-class="active"
    >
      <IconButton label="Set Signature" />
    </router-link>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from "vue";
import { useStore } from "vuex";
import InlineSVG from "../presets/GeneralElements/InlineSVG.vue";
import IconButton from "../presets/GeneralElements/IconButton.vue";

export default defineComponent({
  name: "signature-alert",
  components: { InlineSVG, IconButton },
  setup() {
    const store = useStore();
    const userData = computed(() => store.getters.currentUser);

    return {
      userData,
    };
  },
});
</script>
