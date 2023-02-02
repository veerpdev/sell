<template>
  <div
    class="modal fade patient-alert"
    :id="'modal_patient_alert_' + alert.id"
    tabindex="-1"
    aria-hidden="true"
    ref="viewAlertModalRef"
  >
    <div class="modal-dialog modal-dialog-centered mw-650px">
      <div class="modal-content">
        <div class="modal-header" id="kt_modal_add_customer_header">
          <h2 class="fw-bolder">
            <span :class="'svg-icon svg-icon-2hx svg-icon-' + color">
              <inline-svg :src="icon" />
              {{ alert.title }}
            </span>
          </h2>
          <div
            id="kt_modal_add_customer_close"
            data-bs-dismiss="modal"
            class="btn btn-icon btn-sm btn-active-icon-primary"
          >
            <span class="svg-icon svg-icon-1">
              <InlineSVG icon="cross" />
            </span>
          </div>
        </div>
        <div class="modal-body py-10 px-lg-17">
          <div
            class="scroll-y me-n7 pe-7"
            id="kt_modal_view_alert_scroll"
            data-kt-scroll="true"
            data-kt-scroll-activate="{default: false, lg: true}"
            data-kt-scroll-max-height="auto"
            data-kt-scroll-dependencies="#kt_modal_view_alert_header"
            data-kt-scroll-wrappers="#kt_modal_view_alert_scroll"
            data-kt-scroll-offset="300px"
          >
            <div>
              {{ alert.explanation }}
            </div>
            <p class="pt-10">
              <IconText iconSRC="media/icons/duotune/files/fil002.svg">
                {{ alert.created_by_name }} on
                {{ new Date(alert.created_at).toLocaleDateString("en-AU") }}
              </IconText>
            </p>
          </div>
        </div>
        <div class="modal-footer flex-center">
          <!--begin::Button-->
          <button
            type="button"
            data-bs-dismiss="modal"
            class="btn btn-light me-3"
            @click="dismissHandle()"
          >
            Mark Resolved
          </button>
          <!--end::Button-->
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { PatientActions } from "@/store/enums/StorePatientEnums";
import { defineComponent, ref, onMounted, PropType } from "vue";
import icons from "@/core/data/icons";
import IconText from "@/components/presets/GeneralElements/IconText.vue";
import IPatientAlert from "@/store/interfaces/IPatientAlert";
import store from "@/store/index";
export default defineComponent({
  name: "patient-alert-view-modal",
  props: {
    alert: { required: true, type: Object as PropType<IPatientAlert> },
  },
  components: {
    IconText,
  },
  setup(props) {
    const loading = ref<boolean>(false);
    const icon = ref<string>("");
    const color = ref<string>("");

    const dismissHandle = () => {
      store
        .dispatch(PatientActions.ALERT.UPDATE, {
          patient_alert_id: props.alert.id,
          is_dismissed: true,
        })
        .then((data) => {
          store.dispatch(PatientActions.VIEW, data.patient_id);
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    };

    onMounted(() => {
      if (props.alert.alert_level === "WARNING") {
        icon.value = icons.pencil;
        color.value = "warning";
      } else if (props.alert.alert_level === "BLACKLISTED") {
        icon.value = icons.cross;
        color.value = "danger";
      } else {
        icon.value = icons.bell;
        color.value = "primary";
      }
    });

    return {
      loading,
      icon,
      color,
      dismissHandle,
    };
  },
});
</script>
