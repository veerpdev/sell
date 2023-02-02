<template>
  <div class="d-flex align-items-stretch flex-shrink-0">
    <div class="d-flex align-items-center">
      <a
        class="btn btn-icon btn-active-light-primary position-relative w-30px h-30px w-md-40px h-md-40px"
        href="#/mailbox/list"
      >
        <el-badge
          v-if="profileData?.unread_mails?.length"
          :value="profileData?.unread_mails?.length"
          class="item"
        >
          <span class="svg-icon svg-icon-2x">
            <inline-svg src="media/icons/duotune/communication/com002.svg" />
          </span>
        </el-badge>
        <span v-else class="svg-icon svg-icon-2x">
          <inline-svg src="media/icons/duotune/communication/com002.svg" />
        </span>
      </a>
    </div>

    <div
      class="d-flex align-items-center ms-1 ms-lg-3"
      id="kt_header_user_menu_toggle"
    >
      <div
        class="cursor-pointer symbol symbol-30px symbol-md-40px"
        data-kt-menu-trigger="click"
        data-kt-menu-attach="parent"
        data-kt-menu-placement="bottom-end"
        data-kt-menu-flip="bottom"
      >
        <img
          :src="
            profileData.photo == undefined
              ? 'media/avatars/blank.png'
              : userPhoto
          "
          :alt="profileData.full_name"
        />
      </div>
      <UserMenu :profile-data="profileData" :user-photo="userPhoto"></UserMenu>
    </div>

    <div
      class="d-flex align-items-center d-lg-none ms-2 me-n3"
      title="Show header menu"
    >
      <button
        class="btn btn-icon btn-active-light-primary btn-custom w-30px h-30px w-md-40px h-md-40px"
        id="kt_header_menu_mobile_toggle"
      >
        <span class="svg-icon svg-icon-1">
          <inline-svg src="media/icons/duotune/text/txt001.svg" />
        </span>
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, onMounted, watch, ref } from "vue";
import { useStore } from "vuex";
import UserMenu from "@/layout/header/partials/UserMenu.vue";
import { Actions } from "@/store/enums/StoreEnums";
import IUserProfile from "@/store/interfaces/IUserProfile";
import store from "@/store";
export default defineComponent({
  name: "layout-top-bar",
  components: {
    UserMenu,
  },
  setup() {
    const store = useStore();
    const profileData = computed<IUserProfile>(() => store.getters.userProfile);
    const userPhoto = ref("");
    userPhoto.value = "media/avatars/blank.png";

    watch(profileData, () => {
      if (profileData.value)
        if (
          profileData.value.photo !== null &&
          profileData.value.photo !== ""
        ) {
          store
            .dispatch(Actions.FILE.VIEW, {
              path: profileData.value.photo,
            })
            .then((data) => {
              const blob = new Blob([data], { type: "application/image" });
              const objectUrl = URL.createObjectURL(blob);
              userPhoto.value = objectUrl;
            });
        }
    });

    onMounted(() => {
      store.dispatch(Actions.PROFILE.VIEW);
    });

    return {
      profileData,
      userPhoto,
    };
  },
});
</script>
