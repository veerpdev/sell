<template>
  <div class="card">
    <div class="card-header border-0 pt-6">
      <!--begin::Card title-->
      <div class="card-title">
        <!--begin::Search-->
        <span>Create Health Fund</span>
        <!--end::Search-->
      </div>
      <!--begin::Card title-->
    </div>
    <div class="card-body pt-0">
      <el-form
        @submit.prevent="submit()"
        :model="formData"
        :rules="rules"
        ref="formRef"
      >
        <!--begin::Scroll-->
        <div class="scroll-y me-n7 pe-7 row">
          <div class="col-sm-6 mb-7">
            <!--begin::Label-->
            <label class="required fs-6 fw-bold mb-2">Fund Code</label>
            <!--end::Label-->

            <!--begin::Input-->
            <el-form-item prop="fundCode">
              <el-input
                v-model="formData.fundCode"
                type="text"
                placeholder="Fund Code"
              />
            </el-form-item>
            <!--end::Input-->
          </div>

          <div class="col-sm-6 mb-7">
            <!--begin::Label-->
            <label class="required fs-6 fw-bold mb-2">Fund Name</label>
            <!--end::Label-->

            <!--begin::Input-->
            <el-form-item prop="fundName">
              <el-input
                v-model="formData.fundName"
                type="text"
                placeholder="Fund Name"
              />
            </el-form-item>
            <!--end::Input-->
          </div>

          <div class="col-sm-6 mb-7">
            <!--begin::Label-->
            <label class="required fs-6 fw-bold form-label mb-2"
              >Online Submission To Eclipse:</label
            >
            <!--end::Label-->

            <!--begin::Input-->
            <el-form-item prop="onlineSubmission">
              <el-select v-model="formData.onlineSubmission" class="d-block">
                <el-option value="1" key="yes" label="YES" />
                <el-option value="0" key="no" label="NO" />
              </el-select>
            </el-form-item>
            <!--end::Input-->
          </div>

          <div class="col-sm-6 mb-7">
            <!--begin::Label-->
            <label class="required fs-6 fw-bold form-label mb-2">Status:</label>
            <!--end::Label-->

            <!--begin::Input-->
            <el-select v-model="formData.onlineSubmission" class="d-block">
              <el-option value="1" key="enable" label="ENABLE" />
              <el-option value="0" key="disable" label="DISABLE" />
            </el-select>
            <!--end::Input-->
          </div>

          <div class="col-sm-6 mb-7">
            <!--begin::Label-->
            <label class="required fs-6 fw-bold form-label mb-2"
              >Eclipse Capabilities:</label
            >
            <!--end::Label-->

            <!--begin::Input-->
            <el-form-item prop="tags">
              <el-select
                class="d-block"
                v-model="formData.eclipseCapabilities"
                multiple
                filterable
                default-first-option
                placeholder=""
              >
                <el-option
                  v-for="item in eclipseCapabilities"
                  :value="item.value"
                  :key="item.key"
                  :label="item.label"
                />
              </el-select>
            </el-form-item>
            <!--end::Input-->
          </div>

          <div class="col-sm-6 mb-7">
            <!--begin::Label-->
            <label class="required fs-6 fw-bold mb-2">Phone</label>
            <!--end::Label-->

            <!--begin::Input-->
            <el-form-item prop="phone">
              <el-input
                v-model="formData.phone"
                type="password"
                placeholder="Confirm Password"
              />
            </el-form-item>
            <!--end::Input-->
          </div>
        </div>
        <!--end::Scroll-->

        <div class="modal-footer flex-center">
          <!--begin::Button-->
          <button
            type="reset"
            id="kt_modal_add_customer_cancel"
            class="btn btn-light me-3"
          >
            Discard
          </button>
          <!--end::Button-->

          <!--begin::Button-->
          <button
            :data-kt-indicator="loading ? 'on' : null"
            class="btn btn-lg btn-primary"
            type="submit"
          >
            <span v-if="!loading" class="indicator-label">
              Submit
              <span class="svg-icon svg-icon-3 ms-2 me-0">
                <inline-svg src="media/icons/duotune/arrows/arr064.svg" />
              </span>
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
      </el-form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import eclipseCapabilities from "@/core/data/eclipse-capabilities";

export default defineComponent({
  name: "add-health-fund",

  components: {},

  setup() {
    const formRef = ref<null | HTMLFormElement>(null);
    const addAdminModalRef = ref<null | HTMLElement>(null);
    const loading = ref<boolean>(false);
    const formData = ref({
      fundCode: "",
      fundName: "",
      onlineSubmission: "",
      status: "",
      phone: "",
      eclipseCapabilities: "",
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }
    };

    const rules = ref({
      firstname: [
        {
          required: true,
          message: "First Name is required",
          trigger: "change",
        },
      ],
      lastname: [
        {
          required: true,
          message: "Last Name is required",
          trigger: "change",
        },
      ],
      username: [
        {
          required: true,
          message: "Username is required",
          trigger: "change",
        },
      ],
      email: [
        {
          required: true,
          message: "Email is required",
          trigger: "change",
        },
      ],
      password: [
        {
          required: true,
          message: "Password is required",
          trigger: "change",
        },
      ],
      repassword: [
        {
          required: true,
          message: "Confirm Password is required",
          trigger: "change",
        },
      ],
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs("Create Health Fund", [
        "Settings",
        "Health Fund",
      ]);
    });

    return {
      formData,
      rules,
      submit,
      eclipseCapabilities,
    };
  },
});
</script>
