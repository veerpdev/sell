<template>
  <div
    class="modal fade"
    :id="'modal_' + modalId"
    tabindex="-1"
    aria-hidden="true"
    ref="bModalRef"
    :data-bs-backdrop="isStatic ? 'static' : 'none'"
  >
    <div
      :class="'modal-dialog modal-dialog-centered'"
      :style="'max-width: ' + (width ? width : '850px') + '!important'"
    >
      <div class="modal-content">
        <div
          class="modal-header d-flex flex-row"
          :id="'kt_modal_' + modalId + '_header'"
        >
          <HeadingText :text="title" />
          <div class="d-flex flex-row justify-content-end">
            <slot name="header-actions"></slot>
            <div
              :id="'kt_modal_' + modalId + '_close'"
              data-bs-dismiss="modal"
              class="btn btn-icon btn-sm btn-active-icon-primary"
            >
              <span class="svg-icon svg-icon-1">
                <InlineSVG icon="cross" />
              </span>
            </div>
          </div>
        </div>
        <div class="modal-body">
          <slot></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { ref, onMounted } from "vue";

export default {
  props: {
    title: { type: String, required: true },
    modalId: { type: String, required: true },
    modalRef: { required: false },
    updateRef: { type: Function, required: false },
    isStatic: { type: Boolean, required: false },
    width: { type: String, required: false },
    height: { type: String, required: false },
  },
  setup(props) {
    const bModalRef = ref(null);
    onMounted(() => {
      props.updateRef &&
        typeof props.updateRef !== "string" &&
        props.updateRef(bModalRef.value);
    });
    return {
      bModalRef,
    };
  },
};
</script>
