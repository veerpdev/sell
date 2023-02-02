<template>
  <label
    class="btn btn-outline btn-outline-dashed btn-outline-default mb-1 py-1 d-flex align-items-center"
    :for="document.id"
  >
    <em
      v-if="document.is_urgent"
      class="bi bi-exclamation text-danger fs-1"
    ></em>
    <em
      v-if="!document.is_urgent"
      class="bi bi-exclamation fs-1 opacity-0"
    ></em>
    <span class="svg-icon svg-icon-2x me-2">
      <inline-svg
        :src="
          patientDocumentTypes.find(
            (documentType) => documentType.value === document.document_type
          ).icon
        "
      />
    </span>

    <span class="fw-bold text-start">
      <span
        class="text-dark d-block fs-6 mb-1"
        :style="
          document.is_read
            ? {}
            : {
                fontWeight: '700 !important',
              }
        "
      >
        {{ document.document_name }}
      </span>
      <span class="text-gray-400">{{
        moment(document.created_at).format("DD/MM/YYYY HH:mm A")
      }}</span>
    </span>
  </label>
</template>
<script lang="ts">
import patientDocumentTypes from "@/core/data/patient-document-types";
import moment from "moment";
export default {
  props: {
    document: { required: true, type: Object },
  },
  setup: () => {
    return { patientDocumentTypes, moment };
  },
};
</script>
