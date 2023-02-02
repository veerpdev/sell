<template>
  <CardSection>
    <DocumentList />
  </CardSection>
</template>

<script>
import { defineComponent, watch } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { DocumentActions } from "@/store/enums/StoreDocumentEnums";
import DocumentList from "@/views/specialist/DocumentList.vue";

export default defineComponent({
  name: "admin-outgoing-documents",
  components: { DocumentList },

  setup() {
    const store = useStore();

    // Set the document list to those of the currently logged in specialist
    watch(() => {
      setCurrentPageBreadcrumbs("Outgoing Documents", []);
      store.dispatch(DocumentActions.LIST, {});
    });
  },
});
</script>
