<template>
  <div
    class="modal fade"
    id="modal_eidt_bulletin"
    tabindex="-1"
    aria-hidden="true"
    ref="editBulletinModalRef"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-800px">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_eidt_bulletin_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">{{ formData._title }}</h2>
          <!--end::Modal title-->

          <!--begin::Close-->
          <div
            id="kt_modal_eidt_bulletin_close"
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
              id="kt_modal_eidt_bulletin_scroll"
              data-kt-scroll="true"
              data-kt-scroll-activate="{default: false, lg: true}"
              data-kt-scroll-max-height="auto"
              data-kt-scroll-dependencies="#kt_modal_eidt_bulletin_header"
              data-kt-scroll-wrappers="#kt_modal_eidt_bulletin_scroll"
              data-kt-scroll-offset="300px"
            >
              <InputWrapper prop="title" label="Title" required>
                <el-input
                  v-model="formData.title"
                  type="text"
                  placeholder="title"
                />
              </InputWrapper>

              <InputWrapper prop="body" label="Body" required>
                <el-form-item prop="body">
                  <ckeditor :editor="ClassicEditor" v-model="formData.body" />
                </el-form-item>
              </InputWrapper>
              <!--
              <div class="d-flex">
                <InputWrapper
                  prop="create_by"
                  label="Created by"
                  class="col-6"
                  required
                >
                  <el-select
                    class="w-100"
                    v-model="formData.created_by"
                    filterable
                  >
                    <el-option
                      v-for="item in employeeList"
                      :value="item.id"
                      :label="item.full_name"
                      :key="item.id"
                    />
                  </el-select>
                </InputWrapper>

                <InputWrapper
                  label="Created At"
                  prop="created_at"
                  class="col-6"
                  required
                >
                  <el-date-picker
                    editable
                    class="w-100"
                    v-model="formData.created_at"
                    format="DD-MM-YYYY"
                    placeholder="01-01-1990"
                  />
                </InputWrapper>
              </div>
            --></div>
            <!--end::Scroll-->
          </div>
          <!--end::Modal body-->

          <!--begin::Modal footer-->
          <div class="modal-footer flex-center">
            <button
              type="reset"
              data-bs-dismiss="modal"
              id="kt_modal_add_customer_cancel"
              class="btn btn-light me-3"
            >
              Cancel
            </button>

            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-lg btn-primary"
              type="submit"
            >
              <span v-if="!loading" class="indicator-label">
                {{ formData._button }}
              </span>
              <span v-if="loading" class="indicator-progress">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>
          </div>
          <!--end::Modal footer-->
        </el-form>
        <!--end::Form-->
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, onMounted, computed, ref, watchEffect } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { HRMActions } from "@/store/enums/StoreHRMEnums";
import { Actions } from "@/store/enums/StoreEnums";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import ClassicEditor from "ckeditor5-custom-build/build/ckeditor";
import { useStore } from "vuex";

export default defineComponent({
  name: "bulletin-edit-modal",
  setup() {
    const store = useStore();
    const loading = ref(true);
    const formRef = ref(null);
    const editBulletinModalRef = ref(null);
    const employeeList = computed(() => store.getters.employeeList);
    const bulletin = computed(() => store.getters.getBulletinSelected);

    const formData = ref();

    const rules = ref({
      title: [
        {
          required: true,
          message: "Title cannot be blank",
          trigger: "change",
        },
      ],
      body: [
        {
          required: true,
          message: "Body cannot be blank",
          trigger: "change",
        },
      ],
      created_by: [
        {
          required: true,
          message: "Author cannnot be blank",
          trigger: "change",
        },
      ],
      created_at: [
        {
          required: true,
          message: "Date cannot be blank",
          trigger: "change",
        },
      ],
    });

    watchEffect(() => {
      formData.value = bulletin.value;
    });

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("HRM", ["Manage Bulletins"]);
      store.dispatch(Actions.EMPLOYEE.LIST).then(() => {
        loading.value = false;
      });
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(formData.value._submit, {
              id: formData.value.id,
              title: formData.value.title,
              body: formData.value.body,
              created_by: formData.value.created_by,
              created_at: formData.value.created_at,
            })
            .then(() => {
              loading.value = false;
              store.dispatch(HRMActions.BULLETIN.LIST);
              Swal.fire({
                text: formData.value._submit_text,
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                hideModal(editBulletinModalRef.value);
              });
            })
            .catch(({ response }) => {
              loading.value = false;
              console.log(response.data.error);
            });
          formRef.value.resetFields();
        }
      });
    };

    return {
      loading,
      formData,
      rules,
      ClassicEditor,
      employeeList,
      submit,
      editBulletinModalRef,
      formRef,
    };
  },
});
</script>
