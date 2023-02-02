<template>
  <div class="toolbar d-flex flex-column" id="kt_toolbar">
    <div
      class="w-100 opacity-50 mb-n5 text-center font-weight-bold text-uppercase"
      :style="{ background: bannerColor }"
      v-if="isBanner == 'true'"
    >
      {{ bannerText }}
    </div>
    <!--begin::Container-->
    <div
      id="kt_toolbar_container"
      :class="{
        'container-fluid': toolbarWidthFluid,
        'container-xxl': !toolbarWidthFluid,
      }"
      class="d-flex flex-stack"
    >
      <!--begin::Page title-->
      <div
        data-kt-swapper="true"
        data-kt-swapper-mode="prepend"
        data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
        class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0"
      >
        <!--begin::Title-->
        <h1 class="d-flex align-items-center text-dark fw-bolder my-1 fs-3">
          {{ title }}
        </h1>
        <!--end::Title-->

        <span
          v-if="breadcrumbs"
          class="h-20px border-gray-200 border-start mx-4"
        ></span>

        <!--begin::Breadcrumb-->
        <ul
          v-if="breadcrumbs"
          class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1"
        >
          <template v-for="(item, index) in breadcrumbs" :key="index">
            <li class="breadcrumb-item text-muted">
              {{ item }}
            </li>
            <li class="breadcrumb-item">
              <span class="bullet bg-gray-200 w-5px h-2px"></span>
            </li>
          </template>
          <li class="breadcrumb-item pe-3 text-dark">
            {{ title }}
          </li>
        </ul>
        <!--end::Breadcrumb-->
      </div>
      <!--end::Page title-->

      <!--begin::Actions-->
      <div class="d-flex align-items-center flex-wrap">
        <KTTopbar></KTTopbar>
      </div>
      <!--end::Actions-->
    </div>
    <!--end::Container-->
  </div>
  <!--end::Toolbar-->
  <SignatureAlert />
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { toolbarWidthFluid } from "@/core/helpers/config";
import KTTopbar from "@/layout/header/Topbar.vue";
import SignatureAlert from "@/components/specialist/SignatureAlert.vue";
export default defineComponent({
  name: "KToolbar",
  props: {
    breadcrumbs: Array,
    title: String,
  },
  data: function () {
    return {
      isBanner: process.env.VUE_APP_IS_BANNER,
      bannerText: process.env.VUE_APP_BANNER_TEXT,
      bannerColor: process.env.VUE_APP_BANNER_COLOR,
    };
  },
  components: {
    KTTopbar,
    SignatureAlert,
  },
  setup() {
    return {
      toolbarWidthFluid,
    };
  },
});
</script>
