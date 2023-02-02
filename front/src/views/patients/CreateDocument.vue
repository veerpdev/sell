<template>
  <!--begin::details View-->
  <div class="card mb-5 mb-xl-10" id="patient_view_appointments_current">
    <!--begin::Card header-->
    <div class="card-header cursor-pointer">
      <!--begin::Card title-->
      <div class="card-title m-0">
        <h3 class="fw-bolder m-0">
          Procedure {{ toSentenceCase(documentType.toString()) }} for
          {{ appointmentData?.patient_name.full }}
          ({{
            moment(patientData?.date_of_birth).format("DD/MM/YYYY").toString()
          }})
        </h3>
      </div>
      <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body">
      <div class="mb-8">
        <div class="d-flex flex-column flex-sm-row gap-4">
          <div>
            <div class="d-flex flex-column gap-4">
              <InfoSection heading="Appointment">
                {{ appointmentData?.aus_formatted_date }},
                {{ appointmentData?.appointment_type.name }}
                {{ appointmentData?.start_time }} @
                {{ appointmentData?.clinic?.name }}
              </InfoSection>

              <InfoSection
                v-if="documentType != 'referral'"
                heading="Referring Doctor"
              >
                {{ appointmentData?.referral?.doctor_address_book_name }}
              </InfoSection>
            </div>
          </div>
        </div>
      </div>

      <el-divider />

      <el-form
        @submit.prevent="submit()"
        :model="formData"
        :rules="rules"
        ref="formRef"
      >
        <div class="report-template-wrapper">
          <div class="d-flex flex-row gap-2">
            <InputWrapper
              class="fill-out col-6"
              :label="`${toSentenceCase(documentType.toString())} Template`"
            >
              <el-select
                class="w-100"
                v-model="templateData"
                :placeholder="`Select ${toSentenceCase(
                  documentType.toString()
                )} Template`"
              >
                <el-option
                  v-for="(option, idx) in reportTemplatesData"
                  :key="option.id"
                  :value="idx"
                  :label="option.title"
                />
              </el-select>
            </InputWrapper>

            <InputWrapper
              required
              class="fill-out col-6"
              label="Header/Footer Template"
              prop="header_footer_templates"
            >
              <el-select
                class="col-12"
                v-model="formData.headerFooter"
                placeholder="Select Header/Footer Template"
                props="header_footer_templates_select"
              >
                <el-option :value="0" label="Use Default" />
                <el-option
                  v-for="(option, idx) in headerFooterList"
                  :key="option.id"
                  :value="idx"
                  :label="option.title"
                />
              </el-select>
            </InputWrapper>
          </div>
          <el-divider />

          <template v-if="documentType == 'report'">
            <InputWrapper
              class="fill-out"
              label="Procedures Undertaken"
              prop="procedures_undertaken"
            >
              <el-select
                class="col-12 mt-3"
                placeholder="Select MBS item"
                props="procedures_undertaken_select"
                filterable
                multiple
                :loading="loading"
                :disabled="mbsItems.length === 0"
                v-model="formData.procedures_undertaken"
              >
                <el-option
                  v-for="item in mbsItems"
                  :key="item.id"
                  :value="item.id"
                  :label="getItemName(item)"
                />
              </el-select>
            </InputWrapper>

            <InputWrapper
              class="fill-out"
              label="Extra items used"
              prop="extra_items_used"
            >
              <el-select
                class="col-12 mt-3"
                placeholder="Select MBS item"
                props="extra_items_select"
                filterable
                multiple
                reserve-keyword
                :loading="loading"
                :disabled="mbsItems.length === 0"
                v-model="formData.extra_items_used"
              >
                <el-option
                  v-for="item in mbsItems"
                  :key="item.id"
                  :value="item.id"
                  :label="getItemName(item)"
                />
              </el-select>
            </InputWrapper>

            <InputWrapper
              class="fill-out"
              label="Non-MBS items used"
              prop="admin_items_used"
            >
              <el-select
                class="col-12 mt-3"
                placeholder="Select MBS item"
                props="admin_items_select"
                filterable
                multiple
                reserve-keyword
                :loading="loading"
                :disabled="mbsItems.length === 0"
                v-model="formData.admin_items_used"
              >
                <el-option
                  v-for="item in nonMbsItems"
                  :key="item.id"
                  :value="item.id"
                  :label="getItemName(item)"
                />
              </el-select>
            </InputWrapper>
          </template>

          <template v-else>
            <!-- If the document is anything but a report -->
            <InputWrapper
              v-if="documentType == 'referral'"
              class="fill-out"
              label="Doctor Address Book"
              prop="doctor_address_book"
            >
              <el-autocomplete
                class="w-100"
                v-model="formData.doctor_address_book_name"
                value-key="full_name"
                :fetch-suggestions="searchDoctorAddressBook"
                placeholder="Enter Doctor Name"
                :trigger-on-focus="true"
                @select="handleSelect"
              >
                <template #default="{ item }">
                  <div class="name">
                    {{ item.title }}
                    {{ item.first_name }} {{ item.last_name }}
                  </div>
                  <div class="address">{{ item.address }}</div>
                </template>
              </el-autocomplete>
            </InputWrapper>
          </template>

          <InputWrapper
            class="fill-out"
            label="Include Patient Details:"
            prop="include"
          >
            <div class="d-flex flex-row">
              <el-checkbox
                size="large"
                v-model="formData.patient_demographic"
                :checked="false"
              >
                Demographic
              </el-checkbox>

              <el-checkbox
                size="large"
                v-model="formData.current_medications"
                :checked="false"
              >
                Current Medications
              </el-checkbox>

              <el-checkbox
                size="large"
                v-model="formData.patient_allergies"
                :checked="false"
              >
                Allergies
              </el-checkbox>

              <el-checkbox
                size="large"
                v-model="formData.past_medical_history"
                :checked="false"
              >
                Past Medical history
              </el-checkbox>
            </div>
          </InputWrapper>

          <el-divider />
          <InputWrapper
            class="title-input-wrapper fill-out"
            label="Document title"
            prop="title"
          >
            <el-input
              v-model="formData.title"
              type="text"
              placeholder="Title"
            />
          </InputWrapper>

          <div
            v-for="section in reportSections"
            class="d-flex flex-column gap-2"
            :key="section.id"
          >
            <div class="fv-row">
              <label class="required fs-6 fw-bold mb-2">
                {{ translate(section.title) }}
              </label>

              <el-form-item prop="note">
                <ckeditor
                  v-if="editorConfig"
                  @input="formatAutoTexts"
                  :editor="Editor"
                  v-model="section.free_text_default"
                  :config="editorConfig"
                />
              </el-form-item>
            </div>
          </div>
        </div>

        <div class="d-flex ms-auto justify-content-end">
          <button
            :data-kt-indicator="loading ? 'on' : null"
            class="btn btn-lg btn-primary"
            type="submit"
          >
            <span v-if="!loading" class="indicator-label"> Create </span>
            <span v-if="loading" class="indicator-progress">
              Please wait...
              <span
                class="spinner-border spinner-border-sm align-middle ms-2"
              ></span>
            </span>
          </button>

          <button type="reset" class="btn btn-light-primary ms-2">
            Cancel
          </button>
        </div>
      </el-form>
    </div>
    <!--end::Card body-->
  </div>
  <!--end::details View-->
  <ReportPreviewModal
    v-if="patientData && appointmentData"
    :patient="patientData"
    :appointment="appointmentData"
    :pdfId="documentPreviewFileName"
    :handleSave="handleSave"
  ></ReportPreviewModal>
</template>

<style lang="scss">
.report-template-wrapper {
  .fill-out {
    padding-right: 0px !important;
    padding-left: 0px !important;
  }
  .title-input-wrapper {
    input {
      height: 50px;
      font-weight: bold;
    }
  }
}
</style>

<script lang="ts">
import {
  defineComponent,
  ref,
  onMounted,
  computed,
  watch,
  watchEffect,
} from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { useRouter, useRoute } from "vue-router";
import moment from "moment";
import {
  DocumentActions,
  DocumentMutations,
} from "@/store/enums/StoreDocumentEnums";
import { Actions } from "@/store/enums/StoreEnums";
import IScheduleItem from "@/store/interfaces/IScheduleItem";
import { Modal } from "bootstrap";
import ReportPreviewModal from "@/views/patients/modals/ReportPreviewModal.vue";
import { toSentenceCase } from "@/core/helpers/text";
import IDocumentSection from "@/store/interfaces/IDocumentSection";
import IAppointment from "@/store/interfaces/IAppointment";
import { ElForm } from "element-plus";
import { FormRulesMap } from "element-plus/es/components/form/src/form.type";
import { PatientActions } from "@/store/enums/StorePatientEnums";
import Editor from "ckeditor5-custom-build/build/ckeditor";
import IAutoText from "@/store/interfaces/IAutoText";

export default defineComponent({
  components: {
    ReportPreviewModal,
  },
  setup() {
    const store = useStore();
    const loading = ref<boolean>(false);
    const router = useRouter();
    const route = useRoute();
    const patientId = route.params.patientId;
    const appointmentId = route.params.appointmentId;
    const documentType = route.params.documentType;
    const doctorAddressBooks = computed(
      () => store.getters.getDoctorAddressBookList
    );
    const formRef = ref<typeof ElForm | null>(null);
    const templateData = ref<number | null>(null);
    const appointmentData = ref<IAppointment | null>(null);
    const documentPreviewFileName = ref(null);
    const reportSections = ref<Array<IDocumentSection>>([]);
    const timeout = ref<number | null>(null);
    const editorConfig = ref();
    const patientTranslations = ref();
    const appointmentTranslations = ref();
    const user = computed(() => store.getters.userProfile);
    const autoTexts = computed<IAutoText[]>(() => store.getters.autoTextsList);

    const formData = ref({
      title: "",
      section: [],
      headerFooter: null,
      doctor_address_book_name: "",
      patient_demographic: false,
      current_medications: false,
      patient_allergies: false,
      past_medical_history: false,
      doctor_address_book_id: 0,
      procedures_undertaken: [] as Array<IScheduleItem>,
      extra_items_used: [] as Array<IScheduleItem>,
      admin_items_used: [] as Array<IScheduleItem>,
    });

    const rules = ref<FormRulesMap>({
      title: [
        {
          required: true,
          message: "Title cannot be blank",
          trigger: "change",
        },
      ],
      headerFooter: [
        {
          required: false,
          message: "Header/Footer Template cannot be blank",
          trigger: "change",
        },
      ],
    });

    const scheduleItems = computed(() => store.getters.scheduleItemList);
    const patientData = computed(() => store.getters.selectedPatient);

    const mbsItems = computed(() =>
      scheduleItems.value.filter((item: IScheduleItem) => item.mbs_item_code)
    );

    const nonMbsItems = computed(() =>
      scheduleItems.value.filter((item: IScheduleItem) => !item.mbs_item_code)
    );

    const reportTemplatesData = computed(
      () => store.getters.getDocumentTemplateList
    );

    const headerFooterList = computed(
      () => store.getters.getHeaderFooterTemplateList
    );

    const getItemName = (item: IScheduleItem) => {
      const isMbs = item.mbs_item_code ? true : false;

      if (isMbs) {
        return `${item.mbs_item_code} - ${item.name}`;
      }

      let name = [] as Array<string>;
      if (item.internal_code) {
        name.push(item.internal_code);
      }

      name.push(item.name);

      return name.join(" - ");
    };

    const handlePreviewModal = () => {
      const modal = new Modal(
        document.getElementById("modal_report_document_preview")
      );
      modal.show();
    };

    const searchDoctorAddressBook = (term, cb) => {
      const results = term
        ? doctorAddressBooks.value.filter(createFilter(term))
        : doctorAddressBooks.value;

      if (timeout.value) {
        clearTimeout(timeout.value);
      }
      timeout.value = setTimeout(() => {
        cb(results);
      }, 1000);
    };

    const createFilter = (term) => {
      const keyword = term.toString();
      return (doctorAddressBook) => {
        const full_name =
          doctorAddressBook.title +
          " " +
          doctorAddressBook.first_name +
          " " +
          doctorAddressBook.last_name;
        const full_name_pos = full_name
          .toLowerCase()
          .indexOf(keyword.toLowerCase());
        const address_pos = doctorAddressBook.address
          .toLowerCase()
          .indexOf(keyword.toLowerCase());
        return full_name_pos !== -1 || address_pos !== -1;
      };
    };

    const handleSelect = (item) => {
      formData.value.doctor_address_book_id = item.id;
    };

    const submit = () => {
      loading.value = true;

      if (!formRef.value) {
        loading.value = false;
        return;
      }

      formRef.value.validate((valid) => {
        if (valid) {
          const reportData: Array<Record<string, unknown>> = [];

          reportSections.value.forEach((section) => {
            reportData.push({
              sectionId: section.id,
              free_text_default: section.free_text_default,
              value: formData.value.section["section" + section.id],
            });
          });

          const proceduresUndertaken = [] as Array<Record<string, unknown>>;
          formData.value.procedures_undertaken.forEach((item) => {
            const mbsItem = mbsItems.value.find((mbs) => mbs.id == item);
            proceduresUndertaken.push({
              id: mbsItem.id,
              price: mbsItem.amount,
            });
          });

          const extraItems = [] as Array<Record<string, unknown>>;
          formData.value.extra_items_used.forEach((item) => {
            const mbsItem = mbsItems.value.find((mbs) => mbs.id == item);
            extraItems.push({
              id: mbsItem.id,
              price: mbsItem.amount,
            });
          });

          const adminItems = [] as Array<Record<string, unknown>>;
          formData.value.admin_items_used.forEach((item) => {
            const adminItem = nonMbsItems.value.find(
              (admin) => admin.id == item
            );
            adminItems.push({
              id: adminItem.id,
              price: adminItem.amount,
            });
          });

          const data = {
            title: formData.value.title,
            patient_id: patientId,
            report_data: reportData,
            doctor_address_book:
              documentType == "referral"
                ? formData.value.doctor_address_book_name
                : appointmentData.value?.referral?.doctor_address_book_name,
            appointment_id: appointmentData.value?.id,
            specialist_id: appointmentData.value?.specialist_id,
            document_name: appointmentData.value?.appointment_type_name,
            header_footer_id:
              formData.value.headerFooter != null &&
              formData.value.headerFooter !== 0
                ? headerFooterList.value[formData.value.headerFooter].id
                : null,
            procedures_undertaken: proceduresUndertaken,
            extra_items_used: extraItems,
            admin_items_used: adminItems,
            patient_demographic: formData.value.patient_demographic,
            current_medications: formData.value.current_medications,
            patient_allergies: formData.value.patient_allergies,
            past_medical_history: formData.value.past_medical_history,
            document_type: documentType.toString().toUpperCase(),
          };

          store
            .dispatch(DocumentActions.PATIENT_PREVIEW, data)
            .then((data) => {
              documentPreviewFileName.value = data;
              handlePreviewModal();
            })
            .finally(() => {
              loading.value = false;
            });
        } else {
          loading.value = false;
        }
      });
    };

    const handleSave = (shouldSend = false) => {
      setTimeout(() => {
        loading.value = true;
        if (!formRef.value) {
          loading.value = false;
          return;
        }

        formRef.value.validate((valid) => {
          if (valid) {
            const proceduresUndertaken = [] as Array<Record<string, unknown>>;
            formData.value.procedures_undertaken.forEach((item) => {
              const mbsItem = mbsItems.value.find((mbs) => mbs.id == item);
              proceduresUndertaken.push({
                id: mbsItem.id,
                price: mbsItem.amount,
              });
            });

            const extraItems = [] as Array<Record<string, unknown>>;
            formData.value.extra_items_used.forEach((item) => {
              const mbsItem = mbsItems.value.find((mbs) => mbs.id == item);
              extraItems.push({
                id: mbsItem.id,
                price: mbsItem.amount,
              });
            });

            const adminItems = [] as Array<Record<string, unknown>>;
            formData.value.admin_items_used.forEach((item) => {
              const adminItem = nonMbsItems.value.find(
                (admin) => admin.id == item
              );
              adminItems.push({
                id: adminItem.id,
                price: adminItem.amount,
              });
            });

            const data = {
              title: formData.value.title,
              patient_id: patientId,
              appointment_id: appointmentData.value?.id,
              specialist_id: appointmentData.value?.specialist_id,
              document_name: appointmentData.value?.appointment_type_name,
              procedures_undertaken: proceduresUndertaken,
              extra_items_used: extraItems,
              admin_items_used: adminItems,
              should_send: shouldSend ? 1 : 0,
              file_name: documentPreviewFileName.value,
              patient_demographic: formData.value.patient_demographic,
              current_medications: formData.value.current_medications,
              patient_allergies: formData.value.patient_allergies,
              past_medical_history: formData.value.past_medical_history,
              document_type: documentType.toString().toUpperCase(),
            };

            store.dispatch(DocumentActions.SAVE, data).then((data) => {
              loading.value = false;

              store.commit(DocumentMutations.SET_SELECTED_DOCUMENT, {
                id: data,
              });
              router.push({
                path: "/patients/" + patientId + "/documents",
              });
            });
          }
        });
      }, 1000);
    };

    watch(templateData, () => {
      if (templateData.value !== null) {
        formData.value.title =
          reportTemplatesData.value[templateData.value].title;
        let formattedSections: IDocumentSection[] = [];
        reportTemplatesData.value[templateData.value].sections.forEach(
          (section) => {
            section.free_text_default = translate(section.free_text_default);
            formattedSections.push(section);
          }
        );
        reportSections.value = formattedSections;
      }
    });

    watchEffect(() => {
      if (patientData.value) {
        appointmentData.value = patientData.value.appointments?.find(
          (appointment) => appointment.id === Number(appointmentId)
        );

        patientTranslations.value = [
          {
            key: "[Patient_Name]",
            value: patientData.value.full_name,
          },
        ];
      }
    });

    watch(appointmentData, () => {
      if (appointmentData.value && appointmentData.value.referral) {
        formData.value.doctor_address_book_name =
          appointmentData.value.referral.doctor_address_book_name;
        formData.value.doctor_address_book_id =
          appointmentData.value.referral.doctor_address_book_id;
      }

      appointmentTranslations.value = [
        {
          key: "[Appointment_Type]",
          value: appointmentData.value?.appointment_type_name,
        },
        {
          key: "[Appointment_Date]",
          value: appointmentData.value?.date,
        },
        {
          key: "[Appointment_Time]",
          value: appointmentData.value?.start_time,
        },
        {
          key: "[Appointment_Clinic]",
          value: appointmentData.value?.clinic.name,
        },
        {
          key: "[Appointment_Referring_Doctor]",
          value: appointmentData.value?.referral.doctor_address_book_name,
        },
      ];
    });

    onMounted(() => {
      setCurrentPageBreadcrumbs(toSentenceCase(documentType.toString()), [
        "Patients",
      ]);

      loading.value = true;

      if (Object.keys(patientData.value).length === 0) {
        store.dispatch(PatientActions.VIEW, patientId);
      }

      store.dispatch(
        Actions.DOCUMENT_TEMPLATES.LIST,
        documentType.toString().toUpperCase()
      );

      store.dispatch(Actions.HEADER_FOOTER_TEMPLATE.LIST).then(() => {
        loading.value = false;
      });

      if (documentType == "report") {
        store.dispatch(Actions.SCHEDULE_ITEM.LIST);
      }

      store.dispatch(Actions.AUTO_TEXT.LIST, { user_id: user.value.id });

      editorConfig.value = {
        mention: {
          feeds: [
            {
              marker: "@",
              feed: getAutoCompleteItems,
              minimumCharacters: 0,
              itemRenderer: customItemRenderer,
            },
          ],
        },
      };
    });

    const getAutoCompleteItems = (queryText) => {
      let mentionTexts: { id: string }[] = [];

      autoTexts.value.forEach((autoText) => {
        mentionTexts.push({ id: "@" + autoText.text });
      });

      return mentionTexts.filter(isItemMatching);

      function isItemMatching(item) {
        return item.id.toLowerCase().includes(queryText.toLowerCase());
      }
    };

    function customItemRenderer(item) {
      const itemElement = document.createElement("span");
      let text = item.id;
      text = translate(text);
      itemElement.textContent = text.replaceAll("@", "");

      return itemElement;
    }

    const formatAutoTexts = () => {
      reportSections.value.forEach((section) => {
        if (section.free_text_default.includes('class="mention"')) {
          let text = section.free_text_default;
          text = text.replaceAll("@", "");
          text = text.replaceAll(' class="mention"', "");
          text = translate(text);
          section.free_text_default = text;
        }
      });
    };

    const translate = (text) => {
      patientTranslations.value.forEach((patientTranslation) => {
        text = text.replaceAll(
          patientTranslation.key,
          patientTranslation.value
        );
      });

      appointmentTranslations.value.forEach((appointmentTranslation) => {
        text = text.replaceAll(
          appointmentTranslation.key,
          appointmentTranslation.value
        );
      });

      return text;
    };

    return {
      translate,
      editorConfig,
      mbsItems,
      nonMbsItems,
      formData,
      templateData,
      patientData,
      appointmentData,
      headerFooterList,
      loading,
      reportTemplatesData,
      rules,
      submit,
      formRef,
      getItemName,
      reportSections,
      moment,
      ReportPreviewModal,
      documentPreviewFileName,
      handleSave,
      documentType,
      toSentenceCase,
      searchDoctorAddressBook,
      handleSelect,
      Editor,
      formatAutoTexts,
    };
  },
});
</script>
