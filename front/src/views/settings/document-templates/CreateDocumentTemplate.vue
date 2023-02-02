<template>
  <div
    class="modal fade"
    id="modal_add_document_template"
    tabindex="-1"
    aria-hidden="true"
    ref="createDocumentTemplateModalRef"
    data-bs-backdrop="static"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-800px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_add_customer_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">{{ modalTexts.title }}</h2>
          <!--end::Modal title-->

          <!--begin::Close-->
          <div
            id="kt_modal_add_customer_close"
            data-bs-dismiss="modal"
            class="btn btn-icon btn-sm btn-active-icon-primary"
          >
            <span class="svg-icon svg-icon-1">
              <InlineSVG icon="cross" />
            </span>
          </div>
          <!--end::Close-->
        </div>
        <!--end::Modal header-->
        <!--begin::Form-->
        <el-form
          @submit.prevent="submit()"
          :model="formData"
          :rules="rules"
          ref="formRef"
        >
          <!--begin::Modal body-->
          <div class="modal-body py-10 px-lg-17">
            <!--begin::Scroll-->
            <div
              class="scroll-y me-n7 pe-7"
              id="kt_modal_add_customer_scroll"
              data-kt-scroll="true"
              data-kt-scroll-activate="{default: false, lg: true}"
              data-kt-scroll-max-height="auto"
              data-kt-scroll-dependencies="#kt_modal_add_customer_header"
              data-kt-scroll-wrappers="#kt_modal_add_customer_scroll"
              data-kt-scroll-offset="300px"
            >
              <div class="document-template-wrapper">
                <!--begin::Input group-->
                <div class="fv-row col-12 mb-5">
                  <!--begin::Input-->
                  <el-form-item prop="title">
                    <el-input
                      v-model="formData.title"
                      class="w-100"
                      type="text"
                      placeholder="Document Template Title"
                    />
                  </el-form-item>
                  <!--end::Input-->
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="fv-row col-12 mb-5">
                  <!--begin::Input-->
                  <el-form-item prop="type">
                    <el-select
                      class="w-100"
                      placeholder="Select Document Type"
                      v-model="formData.type"
                    >
                      <template
                        v-for="docType in patientDocumentTemplateTypes"
                        :key="docType.value"
                      >
                        <el-option
                          :value="docType.value"
                          :label="docType.label"
                        >
                          <inline-svg class="me-5" :src="docType.icon" />
                          {{ docType.label }}
                        </el-option>
                      </template>
                    </el-select>
                  </el-form-item>
                  <!--end::Input-->
                </div>
                <!--end::Input group-->

                <div
                  class="border border-5 border-muted mb-10 p-10"
                  v-for="(documentSection, sectionIndex) in formData.sections"
                  :key="sectionIndex"
                >
                  <el-form-item :prop="'section-' + sectionIndex" class="mb-4">
                    <el-input
                      v-model="documentSection.title"
                      class="w-100"
                      type="text"
                      placeholder="Section Title"
                    />
                  </el-form-item>

                  <el-divider class="my-3" />

                  <el-form-item>
                    <label class="text-muted fs-6 fw-bold mb-2 d-block">
                      Free Text Default
                    </label>
                    <ckeditor :editor="ClassicEditor" v-model="formData.body" />
                  </el-form-item>

                  <div class="d-flex mt-3 flex-row-reverse">
                    <LargeIconButton
                      @click="handleDeleteSection(sectionIndex)"
                      text="Delete Section"
                      iconPath="media/icons/duotune/arrows/arr024.svg"
                      :color="'danger'"
                      type="button"
                      iconSize="1"
                    />
                  </div>
                </div>

                <LargeIconButton
                  @click="handleAddSection()"
                  :text="'Add Section'"
                  :iconPath="'media/icons/duotune/arrows/arr024.svg'"
                  :color="'primary'"
                  type="button"
                  iconSize="3"
                />
              </div>
            </div>
            <!--end::Scroll-->
          </div>
          <!--end::Modal body-->

          <!--begin::Modal footer-->
          <div class="modal-footer flex-center">
            <!--begin::Button-->
            <button
              type="reset"
              data-bs-dismiss="modal"
              id="kt_modal_add_document_template_cancel"
              class="btn btn-light me-3"
            >
              Cancel
            </button>
            <!--end::Button-->

            <!--begin::Button-->
            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-lg btn-primary"
              type="submit"
            >
              <span v-if="!loading" class="indicator-label">
                {{ modalTexts.submit }}
              </span>
              <span v-if="loading" class="indicator-progress">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>
            <!--end::Button-->
          </div>
          <!--end::Modal footer-->
        </el-form>
        <!--end::Form-->
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, watchEffect, computed } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import { Actions } from "@/store/enums/StoreEnums";
import { CodingActions } from "@/store/enums/StoreCodingEnums";
import { ElForm } from "element-plus";
import patientDocumentTemplateTypes from "@/core/data/patient-document-template-types";
import ClassicEditor from "ckeditor5-custom-build/build/ckeditor";

export default defineComponent({
  name: "create-document-template-modal",
  setup() {
    const store = useStore();
    const formRef = ref<typeof ElForm | null>(null);
    const createDocumentTemplateModalRef = ref(null);
    const formData = computed(() => store.getters.getDocumentTemplateSelected);
    const loading = ref(false);

    let is_create = false;

    // ICD-10 API seach
    const codes = ref();
    const loadingICD = ref(false);

    const modalTexts = ref<Record<string, string>>({
      title: "",
      submit: "",
    });

    const rules = ref({
      title: [
        {
          required: true,
          message: "Title cannot be blank",
          trigger: "change",
        },
      ],
    });

    const handleAddSection = () => {
      let new_section = {
        title: "",
        free_text_default: "",
      };

      formData.value.sections.push(new_section);
    };

    const handleDeleteSection = (sectionIndex) => {
      formData.value.sections.splice(sectionIndex, 1);
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;

          const action = is_create
            ? Actions.DOCUMENT_TEMPLATES.CREATE
            : Actions.DOCUMENT_TEMPLATES.UPDATE;

          store
            .dispatch(action, formData.value)
            .then(() => {
              hideModal(createDocumentTemplateModalRef.value);
            })
            .finally(() => {
              loading.value = false;

              if (formRef.value) {
                formRef.value.resetFields();
              }
            });
        }
      });
    };

    watchEffect(() => {
      is_create = formData.value.id > 0 ? false : true;

      if (is_create) {
        modalTexts.value.title = "Create Document Template";
        modalTexts.value.submit = "Create";
      } else {
        modalTexts.value.title = "Edit Document Template";
        modalTexts.value.submit = "Update";
      }
    });

    const searchCodes = (query) => {
      if (query) {
        loadingICD.value = true;

        store
          .dispatch(CodingActions.SEARCH_DIAGNOSES, query)
          .then((response) => {
            codes.value = response.data[3];
            loadingICD.value = false;
            console.log(codes.value);
          })
          .catch((response) => {
            console.log(response);
          });
      } else {
        codes.value = [];
      }
    };

    return {
      formData,
      modalTexts,
      rules,
      formRef,
      loading,
      createDocumentTemplateModalRef,
      submit,
      handleAddSection,
      handleDeleteSection,
      searchCodes,
      codes,
      loadingICD,
      patientDocumentTemplateTypes,
      ClassicEditor,
    };
  },
});
</script>
