<template>
  <KTLoader v-if="loaderEnabled" :logo="loaderLogo" />

  <!-- begin:: Body -->
  <div class="page d-flex flex-row flex-column-fluid">
    <!-- begin:: Aside Left -->
    <KTAside
      v-if="asideEnabled"
      :lightLogo="themeLightLogo"
      :darkLogo="themeDarkLogo"
    />
    <!-- end:: Aside Left -->

    <div id="kt_wrapper" class="d-flex flex-column flex-row-fluid wrapper">
      <KTToolbar
        v-if="subheaderDisplay"
        :breadcrumbs="breadcrumbs"
        :title="pageTitle"
      />

      <!-- begin:: Content -->
      <div id="kt_content" class="content d-flex flex-column flex-column-fluid">
        <!-- begin:: Content Body -->
        <div class="post d-flex flex-column-fluid">
          <div id="kt_content_container" class="container-fluid">
            <PasswordExpiredAlert
              v-if="passwordExpired && currentUser.profile.name !== 'Admin'"
            />
            <router-view />
          </div>
        </div>
        <!-- end:: Content Body -->
      </div>
      <!-- end:: Content -->
      <KTFooter />
    </div>
  </div>
  <!-- end:: Body -->
  <KTScrollTop />
  <AppointmentDrawer />
</template>

<script lang="ts">
import {
  defineComponent,
  computed,
  onMounted,
  watch,
  nextTick,
  ref,
} from "vue";
import { useStore } from "vuex";
import { useRoute, useRouter } from "vue-router";
import KTAside from "@/layout/aside/Aside.vue";
import KTFooter from "@/layout/footer/Footer.vue";
import HtmlClass from "@/core/services/LayoutService";
import KTToolbar from "@/layout/toolbar/Toolbar.vue";
import KTScrollTop from "@/layout/extras/ScrollTop.vue";
import KTLoader from "@/components/Loader.vue";
import AppointmentDrawer from "@/components/appointments/AppointmentDrawer.vue";
import { Actions } from "@/store/enums/StoreEnums";
import { MenuComponent } from "@/assets/ts/components";
import { removeModalBackdrop } from "@/core/helpers/dom";
import { reinitializeComponents } from "@/core/plugins/keenthemes";
import PasswordExpiredAlert from "@/components/auth/PasswordExpiredAlert.vue";
import moment from "moment";

import {
  toolbarDisplay,
  loaderEnabled,
  contentWidthFluid,
  loaderLogo,
  asideEnabled,
  subheaderDisplay,
  themeLightLogo,
  themeDarkLogo,
} from "@/core/helpers/config";

export default defineComponent({
  name: "master-layout",
  components: {
    KTAside,
    KTFooter,
    KTToolbar,
    KTScrollTop,
    KTLoader,
    AppointmentDrawer,
    PasswordExpiredAlert,
  },
  setup() {
    const store = useStore();
    const route = useRoute();
    const router = useRouter();
    const currentUser = computed(() => store.getters.currentUser);
    const passwordExpired = ref(false);

    // show page loading
    store.dispatch(Actions.ADD_BODY_CLASSNAME, "page-loading");

    // initialize html element classes
    HtmlClass.init();

    const pageTitle = computed(() => {
      return store.getters.pageTitle;
    });

    const breadcrumbs = computed(() => {
      return store.getters.pageBreadcrumbPath;
    });

    watch(currentUser, () => {
      if (currentUser.value && currentUser.value.profile) {
        if (currentUser.value.profile.name !== "Admin") {
          if (
            moment(currentUser.value.profile.password_changed_date)
              .add(
                currentUser.value.organization?.password_expiration_timeframe,
                "months"
              )
              .unix() < moment().unix()
          ) {
            passwordExpired.value = true;
          }
        }
      }
    });

    onMounted(() => {
      //check if current user is authenticated
      if (!store.getters.isUserAuthenticated) {
        router.push({ name: "sign-in" });
      }

      nextTick(() => {
        reinitializeComponents();
      });

      // Simulate the delay page loading
      setTimeout(() => {
        // Remove page loader after some time
        store.dispatch(Actions.REMOVE_BODY_CLASSNAME, "page-loading");
      }, 1000);
    });

    watch(
      () => route.path,
      () => {
        MenuComponent.hideDropdowns(undefined);

        // check if current user is authenticated
        if (!store.getters.isUserAuthenticated) {
          router.push({ name: "sign-in" });
        }

        nextTick(() => {
          reinitializeComponents();
        });
        removeModalBackdrop();
      }
    );

    return {
      toolbarDisplay,
      loaderEnabled,
      contentWidthFluid,
      loaderLogo,
      asideEnabled,
      subheaderDisplay,
      pageTitle,
      breadcrumbs,
      themeLightLogo,
      themeDarkLogo,
      passwordExpired,
      currentUser,
    };
  },
});
</script>
