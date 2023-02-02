<template>
  <!--begin::Aside-->
  <div
    id="kt_aside"
    class="aside pb-5 pt-5 pt-lg-0 aside"
    data-kt-drawer="true"
    data-kt-drawer-name="aside"
    data-kt-drawer-activate="{default: true, lg: false}"
    data-kt-drawer-overlay="true"
    data-kt-drawer-width="{default:'80px', '300px': '100px'}"
    data-kt-drawer-direction="start"
    data-kt-drawer-toggle="#kt_header_menu_mobile_toggle"
  >
    <!--begin::Brand-->
    <div class="aside-logo py-8" id="kt_aside_logo">
      <!--begin::Logo-->
      <router-link to="/dashboard" class="d-flex align-items-center">
        <!-- <img alt="Logo" src="aurora-sml-logo.png" class="h-75px logo" /> -->
        <img
          alt="Logo"
          :src="logo ? logo : `aurora-sml-logo.png`"
          class="h-75px w-75px logo"
        />
      </router-link>
      <!--end::Logo-->
    </div>
    <!--end::Brand-->

    <!--begin::Aside menu-->
    <div class="aside-menu flex-column-fluid" id="kt_aside_menu">
      <KTMenu />
    </div>
    <!--end::Aside menu-->

    <!--begin::Footer-->
    <div class="aside-footer flex-column-auto" id="kt_aside_footer">
      <!--begin::Menu-->
      <div class="d-flex justify-content-center"></div>
      <!--end::Menu-->
    </div>
    <!--end::Footer-->
  </div>
  <!--end::Aside-->
</template>

<script lang="ts">
import { computed, defineComponent, ref, watch } from "vue";
import KTMenu from "@/layout/aside/Menu.vue";
import { asideTheme } from "@/core/helpers/config";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
export default defineComponent({
  name: "KTAside",
  components: {
    KTMenu,
  },
  props: {
    lightLogo: String,
    darkLogo: String,
  },
  setup() {
    const store = useStore();
    const currentUser = computed(() => store.getters.currentUser);
    const logo = ref("");

    watch(currentUser, () => {
      if (currentUser.value.organization)
        if (
          currentUser.value.organization.logo !== null &&
          currentUser.value.organization.logo !== ""
        ) {
          store
            .dispatch(Actions.FILE.VIEW, {
              path: currentUser.value.organization.logo,
            })
            .then((data) => {
              const blob = new Blob([data], { type: "application/image" });
              const objectUrl = URL.createObjectURL(blob);
              logo.value = objectUrl;
            });
        }
    });

    return {
      asideTheme,
      logo,
    };
  },
});
</script>
