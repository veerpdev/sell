<template>
  <!--begin::Menu-->
  <div
    class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold py-4 fs-6 w-275px"
    data-kt-menu="true"
  >
    <!--begin::Menu item-->
    <div class="menu-item px-3">
      <div class="menu-content d-flex align-items-center px-3">
        <!--begin::Avatar-->
        <div class="symbol symbol-50px me-5">
          <img alt="User Avatar" :src="userPhoto" />
        </div>
        <!--end::Avatar-->

        <!--begin::Username-->
        <div class="d-flex flex-column">
          <div class="fw-bolder d-flex align-items-center fs-5">
            {{ profileData.full_name }}
          </div>
          <span
            class="text-break pe-1 fw-bold text-muted text-hover-primary fs-7"
          >
            {{ profileData.email }}
          </span>
        </div>
        <!--end::Username-->
      </div>
    </div>
    <!--end::Menu item-->

    <!--begin::Menu separator-->
    <div class="separator my-2"></div>
    <!--end::Menu separator-->

    <!--begin::Menu item-->
    <div class="menu-item px-5">
      <a @click="handleProfile" class="menu-link px-5"> Profile </a>
    </div>
    <!--end::Menu item-->
    <!--begin::Menu item-->
    <div v-if="userRole == 'organizationManager'" class="menu-item px-5">
      <a @click="handleSettings" class="menu-link px-5">
        Organisation Settings
      </a>
    </div>
    <!--end::Menu item-->

    <!--begin::Menu item-->
    <div class="menu-item px-5">
      <a @click="signOut()" class="menu-link px-5"> Sign Out </a>
    </div>
    <!--end::Menu item-->
  </div>
  <!--end::Menu-->
</template>

<script lang="ts">
import { defineComponent, PropType, computed } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { Actions } from "@/store/enums/StoreEnums";
import IUserProfile from "@/store/interfaces/IUserProfile";

export default defineComponent({
  name: "kt-user-menu",
  components: {},
  props: {
    profileData: { type: Object as PropType<IUserProfile>, required: true },
    userPhoto: { type: String as PropType<string>, required: true },
  },
  setup(props) {
    const router = useRouter();
    const store = useStore();
    const userRole = computed(() => store.getters.userRole);
    const handleProfile = () => {
      router.push({ name: "profile" });
    };
    const handleSettings = () => {
      router.push({ name: "manager-settings" });
    };

    const signOut = () => {
      store
        .dispatch(Actions.LOGOUT)
        .then(() => router.push({ name: "sign-in" }));
    };

    return {
      handleProfile,
      handleSettings,
      signOut,
      userRole,
    };
  },
});
</script>
