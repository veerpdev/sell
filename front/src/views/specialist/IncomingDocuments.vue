<template>
  <CardSection>
    <DocumentList />
  </CardSection>
</template>

<script>
import { defineComponent, computed, watch } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { useStore } from "vuex";
import { DocumentActions } from "@/store/enums/StoreDocumentEnums";
import DocumentList from "@/views/specialist/DocumentList.vue";

export default defineComponent({
  name: "specialist-incoming-documents",
  components: { DocumentList },

  setup() {
    const store = useStore();
    const userProfile = computed(() => store.getters.userProfile);

    // Set the document list to those of the currently logged in specialist
    watch(userProfile, () => {
      setCurrentPageBreadcrumbs("Incoming Documents", []);
      store.dispatch(DocumentActions.LIST, {
        specialist_id: userProfile.value.id,
      });
    });

    return {};
  },
});
</script>
