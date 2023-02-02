<template>
  <router-view />
</template>

<script lang="ts">
import {
  defineComponent,
  nextTick,
  onMounted,
  onUnmounted,
  watchEffect,
} from "vue";
import { useStore } from "vuex";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import { initializeComponents } from "@/core/plugins/keenthemes";

export default defineComponent({
  name: "app",
  setup() {
    const store = useStore();

    onMounted(() => {
      /**
       * this is to override the layout config using saved data from localStorage
       * remove this to use config only from static config (@/core/config/DefaultLayoutConfig.ts)
       */
      store.commit(Mutations.OVERRIDE_LAYOUT_CONFIG);
      nextTick(() => {
        initializeComponents();
      });
    });
  },
});
</script>

<style lang="scss">
@import "~bootstrap-icons/font/bootstrap-icons.css";
@import "~animate.css";
@import "~sweetalert2/dist/sweetalert2.css";
@import "~@fortawesome/fontawesome-free/css/all.min.css";
@import "~@vueform/multiselect/themes/default.css";
@import "~element-plus/dist/index.css";
@import "assets/sass/plugins";
@import "assets/sass/style";
</style>
