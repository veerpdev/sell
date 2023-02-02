<template>
  <div
    class="modal fade"
    id="modal_coding"
    tabindex="-1"
    aria-hidden="true"
    ref="codingModalRef"
  >
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-100">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header" id="kt_modal_coding_header">
          <!--begin::Modal title-->
          <h2 class="fw-bolder">
            Coding: {{ formData.aus_formatted_date }},
            {{ formData.formatted_appointment_time }}
            {{ formData.appointment_type_name }}
          </h2>
          <!--end::Modal title-->

          <!--begin::Close-->
          <div
            id="kt_modal_coding_close"
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
              id="kt_modal_coding_scroll"
              data-kt-scroll="true"
              data-kt-scroll-activate="{default: false, lg: true}"
              data-kt-scroll-max-height="auto"
              data-kt-scroll-dependencies="#kt_modal_coding_header"
              data-kt-scroll-wrappers="#kt_modal_coding_scroll"
              data-kt-scroll-offset="300px"
            >
              <div class="d-flex flex-row">
                <div class="col-6">
                  <InfoSection heading="Patient"
                    >{{ formData.patient_name.full }} ({{
                      formData.patient_details.date_of_birth
                    }}) </InfoSection
                  ><br />
                  <InfoSection heading="Specialist">
                    {{ formData.specialist_name }} </InfoSection
                  ><br />
                  <InfoSection heading="Clinic">
                    {{ formData.clinic_details.name }} </InfoSection
                  ><br />

                  <div class="row mt-10">
                    <InputWrapper
                      class="col-12"
                      label="Procedures Undertaken"
                      prop="undertaken"
                    >
                      <el-select
                        class="w-100"
                        multiple
                        filterable
                        v-model="formData.undertaken"
                      >
                        <el-option
                          v-for="item in undertaken"
                          :value="item"
                          :label="item"
                          :key="item"
                        />
                      </el-select>
                    </InputWrapper>
                    <InputWrapper
                      class="col-12"
                      label="Extra Items Used"
                      prop="undertaken"
                    >
                      <el-select
                        class="w-100"
                        multiple
                        filterable
                        v-model="formData.extraitems"
                      >
                        <el-option
                          v-for="item in extraitems"
                          :value="item"
                          :label="item"
                          :key="item"
                        />
                      </el-select>
                    </InputWrapper>
                    <InputWrapper
                      class="col-12"
                      label="Diagnosis"
                      prop="diagnosis"
                    >
                      <el-select
                        class="w-100"
                        multiple
                        remote
                        filterable
                        v-model="selected"
                        :remote-method="searchCodes"
                        :loading="loadingICD"
                      >
                        <el-option
                          v-for="item in codes"
                          :key="item[0]"
                          :label="item[0] + ' - ' + item[1]"
                          :value="item[0]"
                        />
                      </el-select>
                    </InputWrapper>
                  </div>
                  <div
                    class="d-flex flex-row justify-content-around gap-3 mt-10 button-group"
                  >
                    <LargeIconButton
                      v-if="formData?.id != aptList[aptList.length - 1]?.id"
                      @click="move(-1)"
                      heading="&lt;&lt; Back"
                      :color="'primary'"
                    />
                    <LargeIconButton
                      @click="submit(0)"
                      heading="Mark Incomplete"
                      :color="'danger'"
                    />

                    <LargeIconButton
                      @click="submit(0)"
                      heading="Mark Complete and close"
                      :color="'success'"
                    />

                    <LargeIconButton
                      v-if="formData?.id != aptList[aptList.length - 1]?.id"
                      @click="submit(0)"
                      heading="Mark Complete and next"
                      :color="'success'"
                    />

                    <LargeIconButton
                      v-if="formData?.id != aptList[aptList.length - 1]?.id"
                      @click="move(+1)"
                      heading="SKIP >>"
                      :color="'primary'"
                    />
                  </div>
                </div>
                <div
                  class="col-6 ml-2 document-viewer"
                  id="documents_viewer"
                ></div>
              </div>
            </div>
            <!--end::Scroll-->
          </div>
          <!--end::Modal body-->
        </el-form>
        <!--end::Form-->
      </div>
    </div>
  </div>
</template>
<script>
import { defineComponent, ref, computed, watch } from "vue";
import { useStore } from "vuex";
import { hideModal } from "@/core/helpers/dom";
import { CodingActions, CodingMutations } from "@/store/enums/StoreCodingEnums";
import { Actions } from "@/store/enums/StoreEnums";
import pdf from "pdfobject";

export default defineComponent({
  name: "coding-modal",
  components: {},
  setup() {
    const store = useStore();
    const formRef = ref(null);
    const codingModalRef = ref(null);
    const loading = ref(false);
    const apt = computed(() => store.getters.getCodingAptSelect);
    const aptList = computed(() => store.getters.getCodingAptList);
    const undertaken = ref(["UA", "UB", "UC", "UD", "UE"]);
    const extraitems = ref(["EA", "EB", "EC", "ED", "EE"]);
    const diagnosis = ref(["DA", "DB", "DC", "DD", "DE"]);

    // ICD-10 API seach
    const codes = ref();
    const selected = ref(null);
    const loadingICD = ref(false);

    const formData = ref({
      id: null,
      patient_name: {
        full: null,
      },
      patient_details: {
        date_of_birth: null,
      },
      patient: {
        gender: null,
      },
      date: null,
      formatted_appointment_time: null,
      specialist_name: null,
      appointment_type_name: null,
      clinic_details: {
        name: null,
      },
      undertaken,
      extraitems,
      diagnosis,
    });

    const searchCodes = (query) => {
      if (query) {
        loadingICD.value = true;

        store
          .dispatch(CodingActions.SEARCH_DIAGNOSES, query)
          .then((response) => {
            codes.value = response.data[3];
            loadingICD.value = false;
          })
          .catch((response) => {
            console.log(response);
          });
      } else {
        codes.value = [];
      }
    };

    const rules = ref({
      undertaken: [
        {
          required: false,
          message: "Procedures cannot be blank",
          trigger: "change",
        },
      ],
      extraitems: [
        {
          required: false,
          message: "Extra Items cannot be blank",
          trigger: "change",
        },
      ],
      diagnosis: [
        {
          required: false,
          message: "Diagnosis cannot be blank",
          trigger: "change",
        },
      ],
    });

    watch(apt, () => {
      formData.value = apt.value;
      if (apt.value && apt.value.documents?.length) {
        const doc = formData.value.documents[0];
        document.getElementById("documents_viewer").innerHTML = "";
        store
          .dispatch(Actions.FILE.VIEW, {
            path: doc.file_path,
          })
          .then((data) => {
            if (doc.file_type === "PDF") {
              let blob = new Blob([data], { type: "application/pdf" });
              let objectUrl = URL.createObjectURL(blob);
              pdf.embed(objectUrl + "#toolbar=0", "#documents_viewer");
            } else if (doc.file_type === "PNG") {
              document.getElementById("documents_viewer").innerHTML =
                "<img src='" + data + "' />";
            }
          });
      }
    });

    const move = (direction) => {
      aptList.value.map((a, i) => {
        if (formData.value.id == a.id) {
          store.commit(
            CodingMutations.SET_SELECT,
            aptList.value[i + direction]
          );
          return;
        }
      });
    };

    const submit = (f) => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          const Data = {
            id: formData.value.id,
            patient_id: formData.value.patient_id,
            clinic_id: formData.value.clinic_id,
            appointment_type_id: formData.value.appointment_type_id,
            appointment_id: formData.value.appointment_id,
            undertaken: formData.value.undertaken,
            extraitems: formData.value.extraitems,
            indications: formData.value.indications,
            diagnosis: formData.value.diagnosis,
          };
          loading.value = true;
          store
            .dispatch(CodingActions.COMPLETE, Data)
            .then(() => {
              loading.value = false;
              store.dispatch(CodingActions.LIST).then(() => {
                if (!f) hideModal(codingModalRef.value);
                else {
                  aptList.value.map((a, i) => {
                    if (formData.value.id == a.id) {
                      store.commit(
                        CodingMutations.SET_SELECT,
                        aptList.value[i + 1]
                      );
                      return;
                    }
                  });
                }
              });
            })
            .catch(({ response }) => {
              loading.value = false;
              console.log(response.data.error);
            });
          formRef.value.resetFields();
        } else {
          // this.context.commit(Mutations.PURGE_AUTH);
        }
      });
    };

    return {
      codes,
      searchCodes,
      selected,
      loadingICD,
      formData,
      rules,
      submit,
      formRef,
      loading,
      codingModalRef,
      undertaken,
      extraitems,
      diagnosis,
      aptList,
      move,
    };
  },
});
</script>
