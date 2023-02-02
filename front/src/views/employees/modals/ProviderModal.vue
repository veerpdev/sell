<template>
  <div
    class="modal fade"
    id="modal_employee_provider"
    tabindex="-1"
    aria-hidden="true"
    ref="employeeProviderModalRef"
    data-bs-backdrop="static"
  >
    <div class="modal-dialog modal-dialog-centered mw-650px">
      <div class="modal-content">
        <div class="modal-header" id="kt_modal_provider_header">
          <h2 class="fw-bolder">Employee Provider Number</h2>
          <div
            id="kt_modal_provider_close"
            data-bs-dismiss="modal"
            class="btn btn-icon btn-sm btn-active-icon-primary"
          >
            <span class="svg-icon svg-icon-1">
              <InlineSVG icon="cross" />
            </span>
          </div>
        </div>
        <el-form @submit.prevent="submit()" :model="formData" ref="formRef">
          <div class="modal-body py-10 px-lg-17">
            <div
              class="scroll-y me-n7 pe-7"
              id="kt_modal_provider_scroll"
              data-kt-scroll="true"
              data-kt-scroll-activate="{default: false, lg: true}"
              data-kt-scroll-max-height="auto"
              data-kt-scroll-dependencies="#kt_modal_provider_header"
              data-kt-scroll-wrappers="#kt_modal_provider_scroll"
              data-kt-scroll-offset="300px"
            >
              <div
                class="row"
                v-for="(provider, index) in formData"
                :key="index"
              >
                <el-form-item
                  class="col-6 d-block"
                  label="Clinic"
                  :prop="'clinic_id'"
                >
                  <el-select
                    class="w-100"
                    type="text"
                    v-model="provider.clinic_id"
                    :prop="'location_select_' + index"
                  >
                    <el-option
                      v-for="clinic in clinicsList"
                      :disabled="
                        formData.filter((f) => f.clinic_id == clinic.id)?.length
                      "
                      :value="clinic.id"
                      :label="clinic.name"
                      :key="clinic.id"
                    />
                  </el-select>
                </el-form-item>

                <el-form-item
                  :class="'col-6 d-block ' + (true ? '' : 'is-error')"
                  label="Provider Number"
                  :prop="'provider_' + idnex + '_provider_number'"
                >
                  <el-input
                    v-model="provider.provider_number"
                    v-mask="'#######A'"
                    type="text"
                    placeholder="Enter Provider Number"
                  />
                  <div v-if="false" class="el-form-item__error">
                    This field cannot be blank {{ clinicsList.length }}
                  </div>
                </el-form-item>
              </div>
              <el-divider v-if="formData.length < clinicsList.length" />

              <LargeIconButton
                v-if="formData.length < clinicsList.length"
                @click="handleAddOtherNumber()"
                heading="Add Provider Number"
                :iconPath="'media/icons/duotune/arrows/arr075.svg'"
                :color="'success'"
                justify="center"
                iconSize="3"
              />
            </div>
          </div>
          <div class="modal-footer flex-center">
            <button
              type="reset"
              data-bs-dismiss="modal"
              class="btn btn-light me-3"
            >
              Cancel
            </button>
            <button
              :data-kt-indicator="loading ? 'on' : null"
              class="btn btn-lg btn-primary m-6"
              type="submit"
            >
              <span v-if="!loading" class="indicator-label"> Save </span>
              <span v-if="loading" class="indicator-progress">
                Please wait...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>
          </div>
        </el-form>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted, watch } from "vue";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import { hideModal } from "@/core/helpers/dom";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { mask } from "vue-the-mask";

export default defineComponent({
  name: "edit-employee-provider-number-modal",
  directives: {
    mask,
  },
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const employeeProviderModalRef = ref(null);
    const loading = ref(false);
    const clinicsList = computed(() => store.getters.clinicsList);
    const employee = computed(() => store.getters.employeeSelected);

    const formData = ref([
      {
        clinic_id: null,
        specilasit_id: null,
        provider_number: null,
      },
    ]);

    const rules = ref({
      clinic_id: [
        {
          required: true,
          message: "This field cannot be blank",
          trigger: ["blur", "change"],
        },
      ],
      provider_number: [
        {
          required: true,
          message: "This field cannot be blank",
          trigger: "blur",
        },
      ],
    });

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          loading.value = true;
          let provideres = formData.value.filter(
            (f) => f.clinic_id != null && f.provider_number != null
          );

          store
            .dispatch(Actions.EMPLOYEE.UPDATE, {
              ...employee.value,
              specialist_clinic_relations: provideres.length ? provideres : [],
            })
            .then(() => {
              loading.value = false;
              Swal.fire({
                text: "Successfully Created!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                hideModal(employeeProviderModalRef.value);
              });
            })
            .catch(() => {
              //
            });
          formRef.value.resetFields();
        } else {
          // this.context.commit(Mutations.PURGE_AUTH);
        }
      });
    };

    const handleAddOtherNumber = () => {
      formData.value.push({
        clinic_id: null,
        specilasit_id: null,
        provider_number: null,
      });
    };

    watch(employee, () => {
      formData.value.specilasit_id = employee.value.id;
      if (employee.value.specialist_clinic_relations) {
        formData.value = employee.value.specialist_clinic_relations;
      }
    });

    onMounted(() => {
      store.dispatch(Actions.CLINICS.LIST);
    });

    return {
      formData,
      rules,
      formRef,
      loading,
      clinicsList,
      employeeProviderModalRef,
      submit,
      handleAddOtherNumber,
    };
  },
});
</script>
