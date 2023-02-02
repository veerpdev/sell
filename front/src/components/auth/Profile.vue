<template>
  <!--begin::Navbar-->
  <div class="card pb-9">
    <ProfileNavigation />
    <div class="card-body pt-9 pb-0">
      <!--begin::Details-->
      <el-form
        @submit.prevent="submit()"
        :model="profileFormData"
        :rules="rules"
        ref="profileFormRef"
      >
        <div class="row">
          <div class="col-sm-12">
            <div class="fv-row mb-7">
              <el-form-item label="Photo">
                <div class="d-flex">
                  <img
                    v-if="showOldPhoto && profileFormData.photo"
                    :src="
                      profileFormData.photo.trim().startsWith(`blob:`)
                        ? profileFormData.photo
                        : `media/avatars/blank.png`
                    "
                    className="rounded me-2"
                    width="146"
                    height="146"
                    alt="profile photo"
                  />

                  <el-upload
                    action="#"
                    ref="upload"
                    list-type="picture-card"
                    :class="{ disabled: uploadDisabled }"
                    :on-change="handleChange"
                    :on-remove="handleRemove"
                    :on-preview="handlePreview"
                    :auto-upload="false"
                    accept="image/*"
                  >
                    <em class="fa fa-plus"></em>
                  </el-upload>

                  <el-dialog v-model="dialogVisible">
                    <img
                      class="w-100 h-100"
                      :src="dialogImageUrl"
                      alt="Preview Image"
                    />
                  </el-dialog>
                </div>
              </el-form-item>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="fv-row mb-7">
              <label class="text-muted fs-6 fw-bold mb-2 d-block"
                >First Name</label
              >
              <el-form-item prop="first_name">
                <el-input
                  type="text"
                  class="w-100"
                  v-model="profileFormData.first_name"
                  placeholder="First Name"
                />
              </el-form-item>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="fv-row mb-7">
              <label class="text-muted fs-6 fw-bold mb-2 d-block"
                >Last Name</label
              >
              <el-form-item prop="last_name">
                <el-input
                  type="text"
                  class="w-100"
                  v-model="profileFormData.last_name"
                  placeholder="Last Name"
                />
              </el-form-item>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="fv-row mb-7">
              <label class="text-muted fs-6 fw-bold mb-2 d-block">Email</label>
              <el-form-item prop="email">
                <el-input
                  type="text"
                  class="w-100"
                  v-model="profileFormData.email"
                  placeholder="Email"
                />
              </el-form-item>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="fv-row mb-7">
              <label class="text-muted fs-6 fw-bold mb-2 d-block">Mobile</label>
              <el-form-item prop="mobile_number">
                <el-input
                  type="text"
                  class="w-100"
                  v-mask="'0#-####-####'"
                  v-model="profileFormData.mobile_number"
                  placeholder="Mobile Number"
                />
              </el-form-item>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="fv-row mb-7">
              <label class="text-muted fs-6 fw-bold mb-2 d-block"
                >Address</label
              >
              <el-form-item prop="address">
                <GMapAutocomplete
                  ref="addressRef"
                  :placeholder="profileFormData.address"
                  @place_changed="handleAddressChange"
                  :options="{
                    componentRestrictions: {
                      country: 'au',
                    },
                  }"
                >
                </GMapAutocomplete>
              </el-form-item>
            </div>
          </div>
        </div>
        <div class="d-flex ms-auto justify-content-end w-50">
          <button type="submit" class="btn btn-primary w-100 w-sm-25">
            Save
          </button>
          <!--begin::Button-->
          <router-link
            type="reset"
            to="/booking/dashboard"
            class="btn btn-light-primary w-100 w-sm-25 ms-2"
          >
            Cancel
          </router-link>
          <!--end::Button-->
        </div>
      </el-form>
      <!--end::Details-->
    </div>
  </div>
  <!--end::Navbar-->
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, computed, watch } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import ProfileNavigation from "@/components/auth/ProfileNavigation.vue";
import { mask } from "vue-the-mask";
import { validatePhone } from "@/helpers/helpers";
import IUserProfile from "@/store/interfaces/IUserProfile";
export default defineComponent({
  name: "profile-page-layout",
  directives: {
    mask,
  },
  components: { ProfileNavigation },
  setup() {
    const store = useStore();
    const profileFormRef = ref();

    const rules = ref({
      first_name: [
        {
          required: true,
          message: "First Name cannot be blank",
          trigger: "change",
        },
      ],
      last_name: [
        {
          required: true,
          message: "Last Name cannot be blank",
          trigger: "change",
        },
      ],
      mobile_number: [
        {
          required: true,
          message: "Contact Number cannot be blank",
          trigger: "change",
        },
        { validator: validatePhone, trigger: "blur" },
      ],
      email: [
        {
          required: true,
          message: "Email cannot be blank",
          trigger: "change",
        },
        {
          type: "email",
          message: "Please input correct email address",
          trigger: ["blur", "change"],
        },
      ],
      address: [
        {
          required: true,
          message: "Address cannot be blank",
          trigger: "change",
        },
      ],
    });
    const profileFormData = computed<IUserProfile>(
      () => store.getters.userProfile
    );
    const loading = ref<boolean>(false);
    const uploadDisabled = ref(false);
    const upload = ref();
    const Data = new FormData();
    const dialogImageUrl = ref("");
    const dialogVisible = ref(false);
    const showOldPhoto = ref(true);

    const handleAddressChange = (e) => {
      profileFormData.value.address = e.formatted_address;
    };

    const handleChange = (file) => {
      showOldPhoto.value = false;
      upload.value.clearFiles();
      uploadDisabled.value = false;
      Data.append("photo", file.raw);
      uploadDisabled.value = true;
    };

    const handleRemove = () => {
      uploadDisabled.value = false;
      showOldPhoto.value = true;
    };

    const handlePreview = (uploadFile) => {
      dialogImageUrl.value = uploadFile.url;
      dialogVisible.value = true;
    };

    const loadProfileImage = () => {
      if (profileFormData.value.photo) {
        store
          .dispatch(Actions.FILE.VIEW, {
            path: profileFormData.value.photo,
          })
          .then((data) => {
            const blob = new Blob([data], { type: "application/image" });
            const objectUrl = URL.createObjectURL(blob);
            profileFormData.value.photo = objectUrl;
          })
          .catch(() => {
            console.log("image load error");
          });
      }
    };

    const submit = () => {
      if (!profileFormRef.value) {
        return;
      }

      Object.keys(profileFormData.value).forEach((key) => {
        if (key != "photo" && key != "abn_acn") {
          Data.append(key, profileFormData.value[key]);
        }
      });

      profileFormRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store.dispatch(Actions.PROFILE.UPDATE, Data).finally(() => {
            store.dispatch(Actions.PROFILE.VIEW).then(() => {
              showOldPhoto.value = true;
              upload.value.clearFiles();
            });
            loading.value = false;
          });
        }
      });
    };

    watch(profileFormData, () => {
      loadProfileImage();
    });

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("Profile", []);
      store.dispatch(Actions.PROFILE.VIEW);
    });

    return {
      profileFormData,
      profileFormRef,
      rules,
      upload,
      dialogVisible,
      dialogImageUrl,
      showOldPhoto,
      submit,
      handleAddressChange,
      handleChange,
      handlePreview,
      handleRemove,
      uploadDisabled,
    };
  },
});
</script>
