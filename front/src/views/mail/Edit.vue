<template>
  <section class="card ps-10 pt-10 pb-15 pe-10">
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
    >
      <h3>Edit Mail Draft</h3>
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

      <el-form-item label="Attachment">
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
        <template
          v-for="attachmentLink in formData.attachmentUploaded"
          :key="attachmentLink.url"
        >
          <div
            class="mt-3 d-flex"
            style="line-height: 20px; justify-content: space-between"
          >
            <a class="fs-5" :href="attachmentLink.url" target="_blank">
              <span class="svg-icon svg-icon-2">
                <inline-svg src="media/icons/duotune/files/fil003.svg" />
              </span>
              <span class="ms-3">{{ attachmentLink.fileName }}</span>
            </a>

            <span
              class="svg-icon svg-icon-2 cursor-pointer"
              @click="handleRemoveUploaded(attachmentLink.url)"
            >
              <inline-svg src="media/icons/duotune/general/gen040.svg" />
            </span>
          </div>
        </template>
      </el-form-item>

      <div class="d-flex flex-row-reverse">
        <router-link
          type="reset"
          to="/mailbox/list"
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
import { defineComponent, onMounted, ref, computed, watch } from "vue";
import { useStore } from "vuex";
import Swal from "sweetalert2/dist/sweetalert2.min.js";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { Actions } from "@/store/enums/StoreEnums";
import { useRouter, useRoute } from "vue-router";
import ClassicEditor from "ckeditor5-custom-build/build/ckeditor";

export default defineComponent({
  name: "email-edit",
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
    const route = useRoute();
    const formRef = ref(null);
    const formData = ref({
      to_user_ids: [],
      subject: "",
      body: "",
    });
    const Data = new FormData();
    const uploadDisabled = ref(false);
    const upload = ref(null);
    const dialogImageUrl = ref("");
    const dialogVisible = ref(false);
    const sendableUsers = computed(() => store.getters.getUserList);
    const repliedMails = computed(() => store.getters.getRepliedMails);

    const handleChange = (file, fileList) => {
      upload.value.clearFiles();
      uploadDisabled.value = false;
      Data.append("attachment[" + file.raw.uid + "]", file.raw);
      uploadDisabled.value = fileList.length >= 1;
    };

    const handleRemove = (file, fileList) => {
      Data.delete("attachment[" + file.raw.uid + "]");
      uploadDisabled.value = fileList.length - 1;
    };

    const handleRemoveUploaded = (url) => {
      formData.value.attachmentUploaded =
        formData.value.attachmentUploaded.filter((e) => e.url != url);
    };

    const handlePreview = (uploadFile) => {
      dialogImageUrl.value = uploadFile.url;
      dialogVisible.value = true;
    };

    watch(repliedMails, () => {
      if (repliedMails.value.length > 0) {
        formData.value = Object.assign({}, repliedMails.value[0]);
        formData.value.to_user_ids = JSON.parse(formData.value.to_user_ids);
        formData.value.attachmentUploaded = [];

        formData.value.attachment.forEach((attachmentLink) => {
          const fileName = attachmentLink.substring(
            attachmentLink.lastIndexOf("/") + 1
          );

          const fileInfo = {
            fileName: fileName,
            url: attachmentLink,
          };

          formData.value.attachmentUploaded.push(fileInfo);
        });

        delete formData.value.attachment;
      }
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("View", ["Mail"]);

      const id = route.params.id;

      store.dispatch(Actions.USER_LIST);

      store.dispatch(Actions.MAILS.VIEW, id);
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

          Data.set(
            "attachmentUploaded",
            JSON.stringify(formData.value.attachmentUploaded)
          );

          store
            .dispatch(Actions.MAILS.UPDATE_DRAFT, Data)
            .then(() => {
              store.dispatch(Actions.MAILS.LIST, "all");
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

          Data.set(
            "attachmentUploaded",
            JSON.stringify(formData.value.attachmentUploaded)
          );

          store
            .dispatch(Actions.MAILS.SEND_DRAFT, Data)
            .then(() => {
              Swal.fire({
                text: "Mail Sent Successfully From Draft!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                store.dispatch(Actions.MAILS.LIST, "all");
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
      upload,
      handleChange,
      handleRemove,
      handlePreview,
      handleSave,
      handleRemoveUploaded,
      dialogVisible,
      dialogImageUrl,
      sendableUsers,
      formData,
      submit,
      ClassicEditor,
      formRef,
    };
  },
});
</script>
