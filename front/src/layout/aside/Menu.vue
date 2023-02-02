<template>
  <!--begin::Menu-->
  <div
    id="kt_aside_menu"
    class="menu menu-column menu-title-gray-600 menu-state-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500 fw-bold fs-6"
    data-kt-menu="true"
  >
    <template v-for="(item, i) in MainMenuConfig" :key="i">
      <template v-if="!item.pages">
        <template
          v-if="
            item.heading &&
            (item.heading !== 'Coding' ||
              (item.heading === 'Coding' && hasCoding))
          "
        >
          <div
            :class="{ 'show here': currentActive(item.route) }"
            class="menu-item py-5"
          >
            <router-link class="menu-link menu-center" :to="item.route">
              <el-badge
                :hidden="!item.badge || getBadgeValue(item) == 0"
                :value="getBadgeValue(item)"
                class="item"
              >
                <span
                  v-if="item.svgIcon || item.fontIcon"
                  class="menu-icon me-0"
                >
                  <em
                    v-if="asideMenuIcons === 'font'"
                    :class="item.fontIcon"
                    class="bi fs-2"
                  ></em>

                  <span
                    v-else-if="asideMenuIcons === 'svg'"
                    class="svg-icon svg-icon-4x"
                  >
                    <inline-svg
                      :style="
                        item.reverse ? 'transform: scaleX(-1) scaleY(-1)' : ''
                      "
                      :src="item.svgIcon"
                    />
                  </span>
                </span>
              </el-badge>
              <span class="menu-title">{{ item.heading }}</span>
            </router-link>
          </div>
        </template>
      </template>
      <template v-if="item.pages">
        <div
          v-if="
            item.heading &&
            (item.heading !== 'Coding' ||
              (item.heading === 'Coding' && hasCoding))
          "
          :class="{ 'show here': hasActiveChildren(item.route) }"
          data-kt-menu-trigger="click"
          data-kt-menu-placement="right-start"
          class="menu-item py-3"
        >
          <span class="menu-link menu-center">
            <span v-if="item.svgIcon || item.fontIcon" class="menu-icon me-0">
              <em
                v-if="asideMenuIcons === 'font'"
                :class="item.fontIcon"
                class="bi fs-2"
              ></em>
              <span
                v-else-if="asideMenuIcons === 'svg'"
                class="svg-icon svg-icon-4x"
              >
                <inline-svg :src="item.svgIcon" />
              </span>
            </span>
            <span class="menu-title">{{ item.heading }}</span>
          </span>
          <div
            :class="{ show: hasActiveChildren(item.route) }"
            class="menu-sub menu-sub-dropdown w-225px w-lg-275px px-1 py-4"
          >
            <template v-for="(menuItem, j) in item.pages" :key="j">
              <div
                v-if="menuItem.heading"
                :class="{ show: hasActiveChildren(menuItem.route) }"
                class="menu-item menu-accordion"
                data-kt-menu-sub="accordion"
                data-kt-menu-trigger="click"
              >
                <router-link
                  class="menu-link"
                  active-class="active"
                  :to="menuItem.route"
                >
                  <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                  </span>
                  <span class="menu-title">
                    {{ menuItem.heading }}
                  </span>
                </router-link>
              </div>
            </template>
          </div>
        </div>
      </template>
    </template>
  </div>
  <!--end::Menu-->
</template>

<script lang="ts">
import {
  defineComponent,
  onMounted,
  ref,
  watchEffect,
  computed,
  watch,
} from "vue";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import { asideMenuIcons } from "@/core/helpers/config";
import MenuConfig from "@/core/config/MainMenuConfig";
import IMenuItem from "@/store/interfaces/IMenuItem";
import IUserAuth from "@/store/interfaces/IUserAuth";

export default defineComponent({
  name: "left-menu",
  components: {},
  setup() {
    const route = useRoute();
    const store = useStore();
    const scrollElRef = ref<null | HTMLElement>(null);
    const MainMenuConfig = ref();
    const currentUser = computed<IUserAuth>(() => store.getters.currentUser);
    const hasCoding = ref(false);

    watch(currentUser, () => {
      hasCoding.value = currentUser.value.organization?.has_coding;
    });

    const getBadgeValue = (item: IMenuItem) => {
      if (item.badgeCount == "IncomingDocuments") {
        return currentUser.value.unreadDocuments;
      }
      return 0;
    };

    onMounted(() => {
      if (scrollElRef.value) {
        scrollElRef.value.scrollTop = 0;
      }
      MainMenuConfig.value = MenuConfig[store.getters.userRole];
    });

    watchEffect(() => {
      MainMenuConfig.value = MenuConfig[store.getters.userRole];
    });

    const hasActiveChildren = (match) => {
      return route.path.indexOf(match) !== -1;
    };

    const currentActive = (current) => {
      return route.path === current;
    };

    return {
      hasActiveChildren,
      currentActive,
      MainMenuConfig,
      asideMenuIcons,
      hasCoding,
      getBadgeValue,
    };
  },
});
</script>
