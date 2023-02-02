<template>
  <section class="card ps-10 pt-10 pb-15 pe-10">
    <h3 class="fs-1 text-capitalize mb-5 ms-16">{{ formData.subject }}</h3>
    <div
      class="w-100 mt-10 mb-10"
      v-for="item in sentRepliedMails"
      :key="item.id"
    >
      <div class="d-flex align-items-center text-dark">
        <div v-if="item.photo" class="symbol symbol-35px me-5">
          <span
            class="symbol-label"
            :style="`background-image: url(${item.photo})`"
          >
          </span>
        </div>
        <!--begin::Avatar-->
        <div v-else class="symbol symbol-35px me-5">
          <div class="symbol-label bg-light-danger">
            <span class="text-danger">U</span>
          </div>
        </div>
        <!--end::Avatar-->
        <!--begin::Name-->
        <span :class="`${!item.is_read ? 'fw-bolder' : 'fw-normal'}`">
          <span class="fs-2">{{ item.name }}</span>
          <span class="fs-5">{{ " <" + item.username + ">" }}</span>
        </span>
      </div>
      <div class="mt-5 ms-15">
        <article v-html="item.body"></article>
        <footer v-if="item.attachmentUploaded.length > 0">
          <hr />
          <template
            v-for="attachmentLink in item.attachmentUploaded"
            :key="attachmentLink.url"
          >
            <div class="mb-2">
              <a class="fs-3" :href="attachmentLink.url" target="_blank">
                <span class="svg-icon svg-icon-2">
                  <inline-svg src="media/icons/duotune/files/fil003.svg" />
                </span>
                <span class="ms-3">{{ attachmentLink.fileName }}</span>
              </a>
            </div>
          </template>
        </footer>
      </div>
    </div>
    <div v-if="!formData.isShow" class="d-flex flex-row mt-5 ms-9" style="">
      <button class="btn fs-4 text-primary" @click="handleReply(true)">
        <span>Reply</span>
      </button>
    </div>
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      ref="formRef"
      v-if="formData.isShow"
    >
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
          <el-button class="btn btn-primary">Choose Files</el-button>
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
        <button class="btn btn-light me-3" @click="handleReply(false)">
          <span>Cancel</span>
        </button>

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
  name: "view-edit",
  setup() {
    const rules = ref({
      body: [
        {
          required: true,
          message: "Please write text",
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
      isShow: false,
    });
    const Data = new FormData();
    const uploadDisabled = ref(false);
    const upload = ref(null);
    const dialogImageUrl = ref("");
    const dialogVisible = ref(false);
    const sendableUsers = computed(() => store.getters.getUserList);
    const repliedMails = computed(() => store.getters.getRepliedMails);
    const sentRepliedMails = ref([]);

    const handleReply = (isShow) => {
      formData.value.isShow = isShow;
    };

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
        sentRepliedMails.value = [];

        repliedMails.value.forEach((item) => {
          item.toUserIds = JSON.parse(item.to_user_ids);

          if (item.status != "draft") {
            sentRepliedMails.value.unshift(item);
          }
        });

        repliedMails.value.forEach((mail) => {
          sendableUsers.value.forEach((user) => {
            if (mail.from_user_id == user.id) {
              mail.name = user.first_name + " " + user.last_name;
              mail.username = user.username;
              mail.photo = user.photo;
            }

            mail.attachmentUploaded = [];

            mail.attachment.forEach((attachmentLink) => {
              const fileName = attachmentLink.substring(
                attachmentLink.lastIndexOf("/") + 1
              );

              const fileInfo = {
                fileName: fileName,
                url: attachmentLink,
              };

              mail.attachmentUploaded.push(fileInfo);
            });
          });
        });

        formData.value = Object.assign({}, repliedMails.value[0]);
        formData.value.to_user_ids = [formData.value.from_user_id];
        delete formData.value.attachment;

        if (repliedMails.value[0].status != "draft") {
          formData.value.isShow = false;
          formData.value.body = "";
          formData.value.reply_id = formData.value.id;
          delete formData.value.id;
          formData.value.attachmentUploaded = [];
        } else {
          formData.value.isShow = true;
        }
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

          let actionName = Actions.MAILS.UPDATE_DRAFT;

          if (formData.value.id == undefined) {
            actionName = Actions.MAILS.COMPOSE;
          }

          store
            .dispatch(actionName, Data)
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

          let actionName = Actions.MAILS.SEND_DRAFT;

          if (formData.value.id == undefined) {
            actionName = Actions.MAILS.SEND;
          }

          store
            .dispatch(actionName, Data)
            .then(() => {
              Swal.fire({
                text: "Reply Mail Sent Successfully!",
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
      handleRemoveUploaded,
      handlePreview,
      handleSave,
      handleReply,
      dialogVisible,
      dialogImageUrl,
      sendableUsers,
      sentRepliedMails,
      formData,
      submit,
      ClassicEditor,
      formRef,
    };
  },
});
</script>
