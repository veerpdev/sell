<template>
  <SettingsButton />
  <CardSection>
    <div class="d-flex flex-column">
      <template v-for="template in notificationTemplates" :key="template.id">
        <div class="row">
          <div class="col-auto">
            <button
              @click="handleEdit(template)"
              class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-5"
            >
              <span class="svg-icon svg-icon-3">
                <InlineSVG icon="pencil" />
              </span>
            </button>
          </div>
          <div class="col-auto">
            <span class="text-primary">{{ template.title }}</span> <br />
            <span v-if="template.allow_day_edit == true"
              >Days Before: {{ template.days_before }}<br />
            </span>
            Status: {{ template.status }} <br />
            {{ template.description }} <br />
          </div>
        </div>
      </template>
    </div>
  </CardSection>
  <EditModal></EditModal>
</template>

<script lang="ts">
import { defineComponent, onMounted, computed } from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import EditModal from "@/views/settings/notification-templates/EditNotificationTemplate.vue";
import { Modal } from "bootstrap";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import INotificationTemplate from "@/store/interfaces/INotificationTemplate";
import CardSection from "@/components/presets/GeneralElements/CardSection.vue";
import SettingsButton from "@/components/SettingsButton.vue";
export default defineComponent({
  components: {
    EditModal,
    CardSection,
    SettingsButton,
  },

  setup() {
    const store = useStore();

    const notificationTemplates = computed<INotificationTemplate[]>(
      () => store.getters.getNotificationTemplatesList
    );

    const handleEdit = (item) => {
      store.commit(Mutations.SET_NTF_TEMPLATES.SELECT, item);
      const modal = new Modal(
        document.getElementById("modal_edit_notification_template")
      );
      modal.show();
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Patient Notifications", ["Settings"]);
      store.dispatch(Actions.NTF_TEMPLATES.LIST);
    });

    return { notificationTemplates, handleEdit };
  },
});
</script>
