<template>
  <SettingsButton />
  <!--begin::Form-->
  <el-form
    v-show="isLoaded"
    @submit.prevent="submit()"
    :model="formData"
    ref="formRef"
  >
    <section>
      <template
        v-for="(preAdmissionSection, sectionIndex) in formData.sections"
        :key="'card-section-' + sectionIndex"
      >
        <CardSection>
          <InputWrapper
            required
            label="Section Title"
            :prop="'title-' + sectionIndex"
            :rules="[
              {
                required: preAdmissionSection.title === '',
                message: 'Section Title cannot be blank',
                trigger: ['blur', 'change'],
              },
            ]"
          >
            <el-input
              v-model="preAdmissionSection.title"
              class="w-100"
              type="text"
              autocomplete="off"
              placeholder="Pre Admission Section Title"
            />
          </InputWrapper>
          <label class="fs-6 fw-bold mb-2 px-6" style="color: grey"
            >Section Questions</label
          >
          <template
            v-for="(
              preAdmissionQuestion, questionIndex
            ) in preAdmissionSection.questions"
            :key="questionIndex"
          >
            <div class="d-flex flex-row gap-2">
              <el-form-item
                class="col-auto mb-2px-6 flex items-center text-sm answer-format"
                :prop="'answer-format-' + questionIndex"
              >
                <el-radio-group
                  v-model="preAdmissionQuestion.answer_format"
                  class="mx-2"
                >
                  <el-radio label="TEXT" size="large">Text</el-radio>
                  <el-radio label="BOOLEAN" size="large">Yes/No</el-radio>
                </el-radio-group>
              </el-form-item>
              <el-form-item
                class="w-100"
                :key="'question-' + questionIndex"
                :prop="'question-' + questionIndex"
                :rules="{
                  required: preAdmissionQuestion.text === '',
                  message: 'Question Text cannot be blank',
                  trigger: 'blur',
                }"
              >
                <el-input
                  class="w-100"
                  v-model="preAdmissionQuestion.text"
                  placeholder="Question Text"
                />
              </el-form-item>
              <button
                type="button"
                @click="handleDeleteQuestion(sectionIndex, questionIndex)"
                class="btn btn-icon btn-bg-light btn-active-color-error btn-sm"
              >
                <span class="svg-icon svg-icon-3">
                  <InlineSVG icon="bin" />
                </span>
              </button>
            </div>
          </template>

          <div class="d-flex justify-content-end flex-row">
            <LargeIconButton
              class="mx-3"
              @click="handleAddQuestion(sectionIndex)"
              :heading="'Add a question'"
              :iconPath="'media/icons/duotune/arrows/arr009.svg'"
            />
            <LargeIconButton
              @click="handleDeleteSection(sectionIndex)"
              :heading="'Delete Entire Section'"
              :iconPath="'media/icons/duotune/general/gen027.svg'"
              :color="'danger'"
            />
          </div>
        </CardSection>
      </template>
      <LargeIconButton
        class="p-6 mb-6"
        @click="handleAddSection()"
        :heading="'Add Section'"
        :iconPath="'media/icons/duotune/arrows/arr009.svg'"
      />
    </section>

    <footer class="d-flex flex-row-reverse">
      <button class="btn btn-lg btn-primary" type="submit">
        <span class="indicator-label"> Save </span>
      </button>
      <router-link type="reset" to="/settings" class="btn btn-light me-3">
        Cancel
      </router-link>
    </footer>
  </el-form>
  <!--end::Form-->
</template>
<script>
import { defineComponent, onMounted, ref } from "vue";
import Swal from "sweetalert2/dist/sweetalert2.min.js";
import { useRouter } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import LargeIconButton from "@/components/presets/GeneralElements/LargeIconButton.vue";
import SettingsButton from "@/components/SettingsButton.vue";
export default defineComponent({
  name: "pre-admission-questions",

  components: { LargeIconButton, SettingsButton },

  setup() {
    const store = useStore();
    const router = useRouter();
    const formRef = ref(null);
    const isLoaded = ref(false);
    const formData = ref({
      sections: null,
    });

    const handleAddQuestion = (sectionIndex) => {
      let new_question = {};

      new_question.text = "";
      new_question.answer_format = "TEXT";

      formData.value.sections[sectionIndex].questions.push(new_question);
    };

    const handleDeleteQuestion = (sectionIndex, questionIndex) => {
      formData.value.sections[sectionIndex].questions.splice(questionIndex, 1);
    };

    const handleAddSection = () => {
      let new_section = {
        title: "",
        questions: [
          {
            text: "",
            answer_format: "TEXT",
          },
        ],
      };
      formData.value.sections.push(new_section);
      window.scrollTo({
        top: document.body.scrollHeight,
        behavior: "smooth",
      });
    };

    const handleDeleteSection = (sectionIndex) => {
      Swal.fire({
        text: `Are you sure you want to delete this section?`,
        icon: "question",
        buttonsStyling: false,
        confirmButtonText: "Yes",
        showCancelButton: true,
        cancelButtonText: "No",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-secondary",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          formData.value.sections.splice(sectionIndex, 1);
        }
      });
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }

      formRef.value.validate(async (valid) => {
        if (valid) {
          await store
            .dispatch(
              Actions.ORG_ADMIN.ORGANIZATION.PRE_ADMISSION_SECTION.UPDATE,
              formData.value
            )
            .then((data) => {
              formData.value.sections = data;
              Swal.fire({
                text: " Pre Admission Section Updated!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                router.push({ name: "org-admin-settings" });
              });
            });
        }
      });
    };

    const initFormData = async () => {
      await store
        .dispatch(Actions.ORG_ADMIN.ORGANIZATION.PRE_ADMISSION_SECTION.LIST)
        .then((data) => {
          formData.value.sections = data;
          isLoaded.value = true;
        });
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Procedure Approval Form Questions", [
        "Settings",
      ]);
      initFormData();
    });

    return {
      formData,
      submit,
      formRef,
      isLoaded,
      handleAddQuestion,
      handleDeleteQuestion,
      handleAddSection,
      handleDeleteSection,
      LargeIconButton,
    };
  },
});
</script>
