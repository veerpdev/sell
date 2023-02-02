<template>
  <div class="card pb-9">
    <div class="card-body pt-9 pb-0">
      <el-form
        @submit.prevent="submit()"
        :model="formData"
        :rules="rules"
        ref="formRef"
      >
        <div class="row">
          <InputWrapper class="col-6" required label="From Date">
            <el-form-item prop="date">
              <el-date-picker
                class="w-100"
                v-model="formData.date"
                type="daterange"
                unlink-panels
                range-separator="-"
                start-placeholder="From date"
                end-placeholder="To date"
                :shortcuts="shortcuts"
                format="DD/MM/YYYY"
              />
            </el-form-item>
          </InputWrapper>
          <InputWrapper class="col-6" required label="Report Type">
            <el-form-item prop="type">
              <el-select
                class="w-100"
                placeholder="Select Time frame"
                v-model="formData.coding_type"
              >
                <el-option
                  v-for="item in codingReportTypes"
                  :value="item"
                  :label="item"
                  :key="item"
                />
              </el-select>
            </el-form-item>
          </InputWrapper>
        </div>
        <div class="d-flex ms-auto justify-content-end w-50">
          <button
            :data-kt-indicator="loading ? 'on' : null"
            class="btn btn-lg btn-primary"
            type="submit"
          >
            <span v-if="!loading" class="indicator-label">
              Generate Report
            </span>
            <span v-if="loading" class="indicator-progress">
              Please wait...
              <span
                class="spinner-border spinner-border-sm align-middle ms-2"
              />
            </span>
          </button>
        </div>
      </el-form>
    </div>
  </div>
</template>

<script>
import { defineComponent, onMounted, ref } from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { CodingActions } from "@/store/enums/StoreCodingEnums";
import { codingReportTypes } from "@/core/data/coding-report-types";
import moment from "moment";
import Swal from "sweetalert2/dist/sweetalert2.js";

export default defineComponent({
  name: "coding-reports",
  setup() {
    const store = useStore();
    const loading = ref(false);
    const formRef = ref(null);
    const formData = ref({
      date: [moment().add(-1, "weeks"), moment()],
      coding_type: codingReportTypes[0],
    });

    const rules = ref({
      coding_type: [
        {
          required: true,
          message: "Type cannot be blank",
          trigger: ["blur", "change"],
        },
      ],
    });

    const shortcuts = [
      {
        text: "Last week",
        value: () => {
          const end = moment();
          const start = moment().add(-1, "weeks");
          return [start, end];
        },
      },
      {
        text: "Last month",
        value: () => {
          const end = moment();
          const start = moment().add(-1, "months");
          return [start, end];
        },
      },
      {
        text: "Last 3 months",
        value: () => {
          const end = moment();
          const start = moment().add(-3, "months");
          return [start, end];
        },
      },
    ];

    onMounted(() => {
      setCurrentPageBreadcrumbs("Coding Reports", ["Coding"]);
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          store
            .dispatch(CodingActions.CHECK_APPOINTMENTS_COMPLETE, {
              from_date: moment(formData.value.date[0]).format("YYYY-MM-DD"),
              to_date: moment(formData.value.date[1]).format("YYYY-MM-DD"),
            })
            .then((data) => {
              loading.value = false;
              if (!data) {
                store
                  .dispatch(CodingActions.GENERATE_CODING_REPORT, {
                    from_date: moment(formData.value.date[0]).format(
                      "YYYY-MM-DD"
                    ),
                    to_date: moment(formData.value.date[1]).format(
                      "YYYY-MM-DD"
                    ),
                    type: formData.value.coding_type,
                  })
                  .then((data) => {
                    const blob = new Blob([data], { type: "text/plain" });
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement("a");
                    link.href = url;
                    link.download =
                      formData.value.coding_type +
                      "-" +
                      moment(formData.value.date[0]).format("DD_MM_YYYY") +
                      "-" +
                      moment(formData.value.date[0]).format("DD_MM_YYYY");
                    link.click();
                    URL.revokeObjectURL(link.href);
                  });
              } else {
                Swal.fire({
                  text: "Cannot generate report some appointment are missing information",
                  icon: "warning",
                  buttonsStyling: false,
                  confirmButtonText: "Ok, got it!",
                  customClass: {
                    confirmButton: "btn btn-primary",
                  },
                });
              }
            });
          formRef.value.resetFields();
        }
      });
    };

    return {
      loading,
      formRef,
      formData,
      rules,
      codingReportTypes,
      submit,
      shortcuts,
    };
  },
});
</script>
