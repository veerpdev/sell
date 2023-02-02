<template>
  <SettingsButton />
  <div class="setting-organization-page card w-75 mx-auto">
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
    >
      <div class="row mt-10 me-5 ms-5">
        <div class="col-sm-12">
          <div class="fv-row mb-7 d-flex">
            <el-form-item label="Logo">
              <div class="d-flex">
                <img
                  alt="Organization Logo"
                  v-if="showOldLogo && formData.logo"
                  :src="
                    formData.logo.trim().startsWith(`blob:`)
                      ? formData.logo
                      : `media/avatars/blank.png`
                  "
                  className="rounded me-2"
                  width="146"
                  height="146"
                />

                <el-upload
                  action="#"
                  ref="uploadRef"
                  list-type="picture-card"
                  :on-change="handleUploadChange"
                  :on-remove="handleRemove"
                  :auto-upload="false"
                  accept="image/*"
                >
                  <em class="fa fa-plus"></em>
                </el-upload>
              </div>
            </el-form-item>
            <el-form-item label="Document Logo" class="ms-5">
              <div class="d-flex">
                <img
                  alt="Document Logo"
                  v-if="showOldDocLogo && formData.document_logo"
                  :src="
                    formData.document_logo.trim().startsWith(`blob:`)
                      ? formData.document_logo
                      : `media/avatars/blank.png`
                  "
                  className="rounded me-2"
                  width="146"
                  height="146"
                />

                <el-upload
                  action="#"
                  ref="uploadDocRef"
                  list-type="picture-card"
                  :on-change="handleDocUploadChange"
                  :on-remove="handleDocRemove"
                  :auto-upload="false"
                  accept="image/*"
                >
                  <em class="fa fa-plus"></em>
                </el-upload>
              </div>
            </el-form-item>
          </div>
        </div>
        <InputWrapper
          class="col-sm-4 mb-5"
          required
          label="Organization name"
          prop="name"
        >
          <el-input
            v-model="formData.name"
            type="text"
            placeholder="Organization name"
          />
        </InputWrapper>

        <InputWrapper
          class="col-sm-4 mb-5"
          required
          label="Appointment length"
          prop="appointment_length"
        >
          <el-input
            v-model="formData.appointment_length"
            type="number"
            placeholder="Appointment length"
          />
        </InputWrapper>
      </div>

      <div class="row mt-5 me-5 ms-5">
        <InputWrapper
          class="col-sm-4 mb-5"
          required
          label="Start time"
          prop="start_time"
        >
          <el-time-picker
            class="w-100"
            v-model="formData.start_time"
            arrow-control
            format="HH:mm"
            placeholder="Start time"
          />
        </InputWrapper>

        <InputWrapper
          class="col-sm-4 mb-5"
          required
          label="End time"
          prop="end_time"
        >
          <el-time-picker
            class="w-100"
            v-model="formData.end_time"
            arrow-control
            format="HH:mm"
            placeholder="End time"
          />
        </InputWrapper>
      </div>

      <div class="row mt-5 me-5 ms-5">
        <InputWrapper class="col-sm-6" required label="ABN/ACN" prop="abn_acn">
          <el-input
            v-model="formData.abn_acn"
            maxlength="11"
            type="text"
            placeholder="Organization ABN/ACN"
          />
        </InputWrapper>

        <InputWrapper class="col-sm-6" label="IP Whitelist" prop="ip_whitelist">
          <el-select
            class="w-100"
            multiple
            allow-create
            filterable
            v-model="formData.ip_whitelist"
          >
            <el-option
              v-for="(item, index) in formData.ip_whitelist"
              :value="item"
              :label="item"
              :key="`ip-select-${index}`"
            />
          </el-select>
        </InputWrapper>
      </div>

      <p
        class="fs-6 fw-bold text-warning px-6"
        v-if="initialAppointmentLength != formData.appointment_length"
      >
        Note: Changing the appointment length will not change existing
        appointments.
      </p>

      <div class="d-flex justify-content-end mb-10 me-10">
        <button
          type="button"
          id="setting_organization_form_cancel"
          class="btn btn-light me-3"
          @click="handleCancelButton"
        >
          Cancel
        </button>
        <!--end::Button-->

        <!--begin::Submit Button-->
        <button class="btn btn-lg btn-primary" type="submit">
          <span class="indicator-label"> Update </span>
        </button>
      </div>
      <!--end::Modal footer-->
    </el-form>
  </div>
</template>
<style lang="scss">
.setting-organization-page {
  .avatar-uploader {
    img.avatar {
      cursor: pointer;
      width: 100%;
      position: relative;
      transition: var(--el-transition-duration-fast);
    }
    .el-upload {
      overflow: hidden;
    }
  }
}
</style>
<script lang="ts">
import {
  defineComponent,
  ref,
  onMounted,
  computed,
  watch,
  watchEffect,
} from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import moment from "moment";
import JwtService from "@/core/services/JwtService";
import { validateAbnAcn } from "@/helpers/helpers";
import SettingsButton from "@/components/SettingsButton.vue";

export interface IOrganizationSetting {
  name: string | null;
  start_time: Date | null;
  end_time: Date | null;
  appointment_length: number | null;
  abn_acn: string | null;
  logo?: string | null;
  document_logo?: string | null;
  ip_whitelist: Array<string>;
}

export default defineComponent({
  name: "organization-settings",
  components: { SettingsButton },
  setup() {
    const formRef = ref<HTMLFormElement>();
    const store = useStore();
    const loading = ref<boolean>(false);
    const initialAppointmentLength = ref<string | null>(null);
    const uploadRef = ref();
    const uploadDocRef = ref();
    const formData = ref<IOrganizationSetting>({
      name: null,
      start_time: null,
      end_time: null,
      appointment_length: null,
      abn_acn: null,
      logo: null,
      document_logo: null,
      ip_whitelist: [] as Array<string>,
    });
    const submitData = new FormData();
    const showOldLogo = ref(true);
    const showOldDocLogo = ref(true);

    const rules = ref({
      name: [
        {
          required: true,
          message: "Organization Name is required",
          trigger: "change",
        },
      ],
      start_time: [
        {
          required: true,
          message: "Start Time is required",
          trigger: "change",
        },
      ],
      end_time: [
        {
          required: true,
          message: "End Time is required",
          trigger: "change",
        },
      ],
      appointment_length: [
        {
          required: true,
          message: "Appointment Length is required",
          trigger: "change",
        },
      ],
      abn_acn: [
        {
          required: true,
          message: "ABN/ACN cannot be blank.",
          trigger: "change",
        },
        {
          validator: validateAbnAcn,
          trigger: ["blur"],
        },
      ],
    });
    const currentUser = computed(() => store.getters.currentUser);
    const handleCancelButton = () => {
      formData.value = {
        name: null,
        start_time: null,
        end_time: null,
        abn_acn: null,
        appointment_length: null,
        ip_whitelist: [] as Array<string>,
      };
    };

    const handleUploadChange = (file, uploadFiles) => {
      while (uploadFiles.length) uploadFiles.pop();
      uploadFiles.push(file);
      showOldLogo.value = false;
      uploadRef.value.clearFiles();
      submitData.append("logo", file.raw);
    };

    const handleDocUploadChange = (file, uploadFiles) => {
      while (uploadFiles.length) uploadFiles.pop();
      uploadFiles.push(file);
      showOldDocLogo.value = false;
      uploadDocRef.value.clearFiles();
      submitData.append("document_logo", file.raw);
    };

    const handleRemove = () => {
      showOldLogo.value = true;
    };

    const handleDocRemove = () => {
      showOldDocLogo.value = true;
    };

    const loadLogoImage = (logo = "logo") => {
      if (formData.value[logo]) {
        store
          .dispatch(Actions.FILE.VIEW, {
            path: formData.value[logo],
          })
          .then((data) => {
            const blob = new Blob([data], { type: "application/image" });
            const objectUrl = URL.createObjectURL(blob);
            formData.value[logo] = objectUrl;
          })
          .catch(() => {
            console.log("image load error");
          });
      }
    };

    const submit = () => {
      if (!formRef.value) {
        return false;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;

          submitData.append(
            "start_time",
            moment(formData.value.start_time as Date).format("HH:mm:ss")
          );
          submitData.append(
            "end_time",
            moment(formData.value.end_time as Date).format("HH:mm:ss")
          );

          Object.keys(formData.value).forEach((key) => {
            if (
              key != "logo" &&
              key != "document_logo" &&
              key != "start_time" &&
              key != "end_time"
            ) {
              submitData.append(key, formData.value[key]);
            }
          });

          store
            .dispatch(Actions.ORG_ADMIN.ORGANIZATION.SETTINGS.UPDATE, {
              submitData: submitData,
            })
            .then(() => {
              store.dispatch(Actions.VERIFY_AUTH, {
                api_token: JwtService.getToken(),
              });
              showOldLogo.value = true;
              showOldDocLogo.value = true;
              uploadRef.value.uploadFiles.pop();
              uploadDocRef.value.uploadFiles.pop();
            })
            .finally(() => {
              uploadRef.value.clearFiles();
              uploadDocRef.value.clearFiles();
              loading.value = false;
            });
        }
      });
    };

    watch(currentUser, () => {
      if (currentUser.value.organization) {
        formData.value.name = currentUser.value.organization.name;
        formData.value.abn_acn = currentUser.value.organization.abn_acn;
        formData.value.appointment_length =
          currentUser.value.organization.appointment_length;
        formData.value.start_time = new Date(
          moment().format("YYYY-MM-DD") +
            " " +
            currentUser.value.organization.start_time
        );
        formData.value.end_time = new Date(
          moment().format("YYYY-MM-DD") +
            " " +
            currentUser.value.organization.end_time
        );
        formData.value.ip_whitelist = currentUser.value.organization
          .ip_whitelist
          ? currentUser.value.organization.ip_whitelist
          : [];
        initialAppointmentLength.value =
          currentUser.value.organization.appointment_length;
        formData.value.logo = currentUser.value.organization.logo;
        formData.value.document_logo =
          currentUser.value.organization.document_logo;
        loadLogoImage();
        loadLogoImage("document_logo");
      }
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("Organization Settings", ["Settings"]);
      store.dispatch(Actions.VERIFY_AUTH, {
        api_token: JwtService.getToken(),
      });
    });

    return {
      formData,
      handleCancelButton,
      rules,
      submit,
      formRef,
      uploadRef,
      uploadDocRef,
      loading,
      initialAppointmentLength,
      handleUploadChange,
      handleDocUploadChange,
      handleRemove,
      handleDocRemove,
      showOldLogo,
      showOldDocLogo,
    };
  },
});
</script>
