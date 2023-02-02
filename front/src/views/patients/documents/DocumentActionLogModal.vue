<template>
  <ModalWrapper title="Document action log" modalId="document_action_log">
    <template v-for="(log, index) in outgoingLogs" :key="index">
      <div>
        Sent via {{ log.send_method }}, by {{ log.sending_user_name }}
        {{ moment(log.created_at).format("DD/MM/YYYY").toString() }}
      </div>
    </template>
  </ModalWrapper>
</template>

<script lang="ts">
import { defineComponent, ref, computed } from "vue";
import { useStore } from "vuex";
import moment from "moment";
import IOutgoingMessageLog from "@/store/interfaces/IOutgoingMessageLog";

export default defineComponent({
  name: "send-email-modal",
  components: {},
  props: {
    document: { type: Object, required: true },
  },
  setup() {
    const store = useStore();
    const actionLogModalRef = ref(null);
    const outgoingLogs = computed<IOutgoingMessageLog[]>(
      () => store.getters.getOutgoingList
    );
    const updateRef = (_ref) => {
      actionLogModalRef.value = _ref;
    };

    return {
      actionLogModalRef,
      updateRef,
      outgoingLogs,
      moment,
    };
  },
});
</script>
