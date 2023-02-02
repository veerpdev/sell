<template>
  <section class="card ps-10 pt-10 pb-15 pe-10">
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
    >
      <h3>Compose New Mail</h3>
      <el-form-item prop="to_user_ids">
        <el-select
          class="mt-10 w-100"
          placeholder="To:"
          v-model="formData.to_user_ids"
          filterable
          multiple
        >
          <el-option
            v-for="item in sendableUsers"
            :value="item.id"
            :label="
              item.first_name +
              ' ' +
              item.last_name +
              ' <' +
              item.username +
              '>'
            "
            :key="item.id"
          />
        </el-select>
      </el-form-item>

      <el-form-item prop="subject">
        <el-input
          v-model="formData.subject"
          type="text"
          placeholder="Subject"
        />
      </el-form-item>

      <el-form-item prop="body">
        <ckeditor :editor="ClassicEditor" v-model="formData.body" />
      </el-form-item>

      <!-- <el-form-item label="Attachment">
        <el-upload
          action="#"
          ref="upload"
          class="mr-20"
          multiple
          :on-change="handleChange"
          :on-remove="handleRemove"
          :on-preview="handlePreview"
          :limit="100"
          :auto-upload="false"
        >
          <el-button type="primary" class="btn btn-primary"
            >Choose Files</el-button
          >
        </el-upload>
      </el-form-item> -->

      <div class="d-flex flex-row-reverse">
        <router-link
          type="reset"
          to="/mails"
          id="kt_modal_mail_compose_cancel"
          class="btn btn-light me-3"
        >
          Cancel
        </router-link>

        <button class="btn btn-light me-3" @click="handleSave()">
          <span>Save</span>
        </button>

        <button class="btn btn-lg btn-primary me-3" type="submit">
          <span class="indicator-label">
            Send
            <span class="svg-icon svg-icon-3 ms-2 me-0">
              <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
            </span>
          </span>
        </button>
      </div>
    </el-form>
  </section>
</template>

<script>
import { defineComponent, onMounted, ref, computed } from "vue";
import { useStore } from "vuex";
import Swal from "sweetalert2/dist/sweetalert2.min.js";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { Actions } from "@/store/enums/StoreEnums";
import { useRouter } from "vue-router";
import ClassicEditor from "ckeditor5-custom-build/build/ckeditor";

export default defineComponent({
  name: "email-compose",
  setup() {
    const rules = ref({
      to_user_ids: [
        {
          required: true,
          message: "Please select user",
          trigger: "change",
        },
      ],
    });

    const store = useStore();
    const router = useRouter();
    const formRef = ref(null);
    const formData = ref({
      to_user_ids: [],
      subject: "",
      body: "",
    });
    const Data = new FormData();
    // const uploadDisabled = ref(false);
    // const upload = ref(null);
    // const dialogImageUrl = ref("");
    // const dialogVisible = ref(false);
    const sendableUsers = computed(() => store.getters.getUserList);

    // const handleChange = (file, fileList) => {
    //   upload.value.clearFiles();
    //   uploadDisabled.value = false;
    //   Data.append("attachment[" + file.raw.uid + "]", file.raw);
    //   uploadDisabled.value = fileList.length >= 1;
    // };

    // const handleRemove = (file, fileList) => {
    //   Data.delete("attachment[" + file.raw.uid + "]");
    //   uploadDisabled.value = fileList.length - 1;
    // };

    // const handlePreview = (uploadFile) => {
    //   dialogImageUrl.value = uploadFile.url;
    //   dialogVisible.value = true;
    // };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Compose", ["Mail"]);

      store.dispatch(Actions.USER_LIST);
    });

    const handleSave = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          Object.keys(formData.value).forEach((key) => {
            Data.append(key, formData.value[key]);
          });

          store
            .dispatch(Actions.MAILS.COMPOSE, Data)
            .then(() => {
              router.push({ name: "mailbox-list" });
            })
            .catch(({ response }) => {
              console.log(response.data.error);
            });
        }
      });
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          Object.keys(formData.value).forEach((key) => {
            Data.append(key, formData.value[key]);
          });

          store
            .dispatch(Actions.MAILS.SEND, Data)
            .then(() => {
              Swal.fire({
                text: "Mail Sent Successfully!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                router.push({ name: "mailbox-list" });
              });
            })
            .catch(({ response }) => {
              console.log(response.data.error);
            });
        } else {
          // this.context.commit(Mutations.PURGE_AUTH);
        }
      });
    };

    return {
      rules,
      // upload,
      // handleChange,
      // handleRemove,
      // handlePreview,
      handleSave,
      // dialogVisible,
      // dialogImageUrl,
      sendableUsers,
      formData,
      submit,
      ClassicEditor,
      formRef,
    };
  },
});
</script>
