<!-- eslint-disable prettier/prettier -->
<template>
  <!--begin::Navbar-->
  <div class="card pb-9 signature-page">
    <ProfileNavigation />

    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
    >
      <div class="d-flex flex-row">
        <div class="col-6 p-3">
          <InputWrapper label="Sign Off" prop="sign_off">
            <el-input
              type="text"
              v-model="formData.sign_off"
              placeholder="E.g. Regards,"
            />
          </InputWrapper>
          <InputWrapper label="Subheading" prop="education_code">
            <el-input
              type="text"
              v-model="formData.education_code"
              placeholder="E.g. MBBS"
            />
          </InputWrapper>
  
          <InputWrapper prop="signature" label="Signature">
            <el-radio-group v-model="userDrawBox" size="large">
              <el-radio-button :label="true">Draw</el-radio-button>
              <el-radio-button :label="false">Upload</el-radio-button>
            </el-radio-group>
          </InputWrapper>
          <VueSignaturePad
        v-if="userDrawBox"
        width="500px"
        height="300px"
        ref="signaturePad"
        class="border border-primary border-1 m-6"
        :options="{ onBegin }"
      />
      <InputWrapper v-else required class="full" prop="signature">
        <el-upload
          action="#"
          ref="signatureRef"
          class="signature-uploader"
          list-type="picture-card"
          :auto-upload="false"
          :show-file-list="false"
          :on-change="
            (uploadFile) => {
              handleSignatureUploadSuccess(uploadFile);
            }
          "
        >
          <img
            alt="User Signature"
            v-if="formData.signature"
            :src="formData.signature"
            class="signature"
          />
          <em v-if="!formData.signature" class="el-icon avatar-uploader-icon">
            <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
              <path
                fill="currentColor"
                d="M480 480V128a32 32 0 0 1 64 0v352h352a32 32 0 1 1 0 64H544v352a32 32 0 1 1-64 0V544H128a32 32 0 0 1 0-64h352z"
              ></path>
            </svg>
          </em>
        </el-upload>
      </InputWrapper>

      <div class="ps-6">
        <button
          type="submit"
          :data-kt-indicator="loading ? 'on' : null"
          class="btn btn-primary w-25"
          :disabled="isUpload === null"
        >
          Save
        </button>
        <button
          type="button"
          @click="cancel"
          class="btn btn-light-primary w-25 ms-2"
        >
          Cancel
        </button>
      </div>

        </div>

      <div class="col-6 p-3">
         <!--begin::Details-->
         <InputWrapper label="Example" prop="example">
       <div class="border width-500 mb-2 p-5">
         {{ formData.sign_off }}<br />

         <img alt="User Signature" style="height: 80px" :src="signature" />
         <div class="text-primary p-2idth-500 h6">
           {{ userInfo?.full_name }}
         </div>

         {{ formData.education_code }} <br />

         0000000A
       </div>
       </InputWrapper>

      </div>
       
      </div>

      
     

  
    </el-form>
    <!--end::Details-->
  </div>

  <!--end::Navbar-->
</template>
<style lang="scss">
.width-500 {
  width: 500px !important;
}
.signature-page {
  .signature-uploader {
    img.signature {
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
<script>
import { defineComponent, ref, onMounted, computed, watch } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import ProfileNavigation from "@/components/auth/ProfileNavigation";

export default defineComponent({
  name: "user-signature",
  components: { ProfileNavigation },
  setup() {
    const store = useStore();
    const currentUser = computed(() => store.getters.currentUser);
    const userInfo = ref(null);
    const formRef = ref(null);
    const isUpload = ref(null);
    const signaturePad = ref(null);
    const signature = ref(null);
    const userDrawBox = ref(true);
    const formData = ref({
      signature: null,
      signature_file: null,
      education_code: "",
      sign_off: "",
    });
    const loading = ref(false);

    const getSignatureData = () => {
      return signaturePad.value.saveSignature();
    };

    const clearSignature = () => {
      signaturePad.value.clearSignature();
    };

    const onBegin = (flag = false) => {
      formData.value.signature = null;
      formData.value.signature_file = null;
      isUpload.value = flag;
    };

    const handleSignatureUploadSuccess = async (uploadFile) => {
      formData.value.signature = URL.createObjectURL(uploadFile.raw);
      formData.value.signature_file = await convertBlobToBase64(uploadFile.raw);
      isUpload.value = true;
      clearSignature();
    };

    const blobToBase64 = (blob) =>
      new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onload = () => resolve(reader.result);
        reader.onerror = (error) => reject(error);
      });

    const convertBlobToBase64 = async (blob) => {
      // blob data
      return await blobToBase64(blob);
    };

    const loadSignatureImage = () => {
      store
        .dispatch(Actions.FILE.VIEW, {
          path: currentUser.value.profile.signature,
        })
        .then((data) => {
          const blob = new Blob([data], { type: "application/image" });
          const objectUrl = URL.createObjectURL(blob);
          signature.value = objectUrl;
        })
        .catch(() => {
          console.log("image load error");
        });
    };

    onMounted(() => {
      loadSignatureImage();
    });

    const resetForm = () => {
      clearSignature();
      onBegin(null);
      loadSignatureImage();
    };

    const cancel = () => {
      resetForm();
    };

    const submit = () => {
      let signature = null;
      if (isUpload.value === true) {
        signature = formData.value.signature_file;
      } else {
        const { isEmpty, data } = getSignatureData();
        signature = !isEmpty ? data : null;
      }

      if (signature) {
        loading.value = true;
        store
          .dispatch(Actions.PROFILE.UPDATE_SIGNATURE, {
            signature: signature,
            sign_off: formData.value.sign_off,
            education_code: formData.value.education_code,
          })
          .then(() => {
            loading.value = false;
            store.dispatch(Actions.PROFILE.VIEW);
            resetForm();
          })
          .catch(({ response }) => {
            loading.value = false;
            console.log(response.data.error);
          });
      }
    };

    watch(currentUser, () => {
      console.log("signature");
      console.log(currentUser.value.profile.signature);
      if (currentUser.value && currentUser.value.profile)
        userInfo.value = currentUser.value.profile;
      formData.value.sign_off = currentUser.value.profile.sign_off;
      formData.value.education_code = currentUser.value.profile.education_code;
      loadSignatureImage();
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("Password", ["Profile"]);
    });

    return {
      formData,
      formRef,
      signaturePad,
      getSignatureData,
      submit,
      clearSignature,
      handleSignatureUploadSuccess,
      onBegin,
      isUpload,
      cancel,
      signature,
      userInfo,
      userDrawBox,
    };
  },
});
</script>
