<template>
  <LargeIconButton
    :text="`Create ${toSentenceCase(documentType)}`"
    @click="handleButton()"
    :loading="loading"
    :disabled="disabled"
  />
</template>

<script lang="ts">
import { ref, computed, watch } from "vue";
import { useRouter } from "vue-router";
import { toSentenceCase } from "@/core/helpers/text";
import IDocumentTemplate from "@/store/interfaces/IDocumentTemplate";
import store from "@/store";
export default {
  props: {
    appointment: {
      required: true,
      type: Object,
    },
    patient: {
      required: true,
      type: Object,
    },
    documentType: {
      required: true,
      type: String,
    },
  },
  setup(props) {
    const router = useRouter();
    const loading = ref<boolean>(false);
    const documentTemplates = computed<IDocumentTemplate[]>(
      () => store.getters.getDocumentTemplateList
    );
    const disabled = ref<boolean>(true);
    const handleButton = () => {
      loading.value = true;

      router
        .push({
          name: "patients-document",
          params: {
            patientId: props.patient.id,
            appointmentId: props.appointment.id,
            documentType: props.documentType,
          },
        })
        .finally(() => {
          loading.value = false;
        });
    };

    watch(documentTemplates, () => {
      if (documentTemplates.value) {
        const templates = documentTemplates.value.filter(
          (template) => template.type == props.documentType.toUpperCase()
        );
        disabled.value = templates.length > 0 ? false : true;
      }
    });

    return {
      handleButton,
      toSentenceCase,
      loading,
      disabled,
    };
  },
};
</script>
