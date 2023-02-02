<template>
  <ModalWrapper
    title="Report Document Preview"
    modalId="report_document_preview"
    :updateRef="updateRef"
  >
    <div class="preview-pdf-title">
      <div>
        <p>Patient Name : {{ patient.full_name }}</p>
        <p>Appointment Type : {{ appointment.appointment_type_name }}</p>
        <p>
          Date : {{ appointment.date
          }}{{ appointment.formatted_appointment_time }}
        </p>
      </div>
      <div>
        <p>To : {{ appointment?.referral?.doctor_address_book?.full_name }}</p>
        <p>
          Number : {{ appointment?.referral?.doctor_address_book?.provider_no }}
        </p>
      </div>
    </div>
    <div class="h-450px" id="documentField">
      <div class="fv-row pdf_previewer_wrapper">
        <div id="document-preview" class="pdf_viewer"></div>
      </div>
    </div>
    <div class="preview-pdf-action">
      <div>
        <button
          class="btn btn-lg btn-secondary"
          data-bs-dismiss="modal"
          type="button"
        >
          Edit Report
        </button>
      </div>
      <div class="save-action-container">
        <button
          class="btn btn-lg btn-primary"
          data-bs-dismiss="modal"
          type="button"
          @click="save(true)"
        >
          Save And Send
        </button>
        <button
          class="btn btn-lg btn-primary"
          data-bs-dismiss="modal"
          type="button"
          @click="save(false)"
        >
          Save
        </button>
      </div>
    </div>
  </ModalWrapper>
</template>
<style lang="scss">
.preview-pdf-title,
.preview-pdf-action {
  padding-top: 15px;
  display: flex;
  flex-direction: row;
  > div {
    flex: 1;
  }
  > .save-action-container {
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
    > button {
      margin-left: 15px;
    }
  }
}
.pdf_previewer_wrapper {
  height: 100%;
  > .pdf_viewer {
    height: 100%;
    overflow: auto;
  }
}
.filter-appointment {
  width: calc(100% - 215px);
}
</style>
<script>
import { defineComponent, ref, watchEffect } from "vue";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import pdf from "pdfobject";

export default defineComponent({
  name: "report-preview-modal",
  components: {},
  props: {
    patient: { required: true },
    appointment: { required: true },
    pdfId: { required: true },
    handleSave: { required: true },
  },
  setup(props) {
    const store = useStore();

    const documentReportRef = ref();
    const updateRef = (_ref) => {
      documentReportRef.value = _ref;
    };

    const tempFile = ref();

    const save = (flag) => {
      props.handleSave(flag);
    };

    watchEffect(() => {
      props.pdfId &&
        store
          .dispatch(Actions.FILE.VIEW, {
            path: props.pdfId,
          })
          .then((data) => {
            document.getElementById("document-preview").innerHTML = "";
            tempFile.value = data;
            let blob = new Blob([data], { type: "application/pdf" });
            let objectUrl = URL.createObjectURL(blob);
            pdf.embed(objectUrl + "#toolbar=0", "#document-preview");
          })
          .catch(() => {
            console.log("Document Load Error");
          });
    });

    return {
      documentReportRef,
      updateRef,
      save,
    };
  },
});
</script>
