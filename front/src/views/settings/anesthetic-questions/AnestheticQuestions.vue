<template>
  <SettingsButton />
  <div class="card w-75 mx-auto">
    <div class="card-header row border-0 p-6">
      <div class="card-title col">
        <div class="alert alert-primary d-flex m-auto align-items-center p-2">
          <span class="svg-icon svg-icon-2hx svg-icon-primary me-2">
            <inline-svg src="media/icons/duotune/general/gen007.svg" />
          </span>
          <div class="d-flex flex-column">
            <span
              >These questions will appear when an appointment that requires an
              anesthetists is booked. Should a patient answer yes to any of the
              question - the employee creating the booking will be alerted to
              book a consult</span
            >
          </div>
        </div>
      </div>
      <!--begin::Add button-->
      <div class="card-toolbar col-12 col-sm-2">
        <button
          type="button"
          class="btn btn-light-primary ms-auto"
          data-bs-toggle="modal"
          data-bs-target="#modal_add_anesthetic_question"
        >
          <span class="svg-icon svg-icon-2">
            <InlineSVG icon="plus" />
          </span>
          Add
        </button>
      </div>
      <!--end::Add button-->
    </div>
    <div class="card-body pt-0">
      <Datatable
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="20"
        :enable-items-per-page-dropdown="false"
      >
        <template v-slot:cell-question="{ row: item }">
          <button
            @click="handleEdit(item)"
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
          >
            <span class="svg-icon svg-icon-3">
              <InlineSVG icon="pencil" />
            </span>
          </button>

          <button
            @click="handleDelete(item.id)"
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-5"
          >
            <span class="svg-icon svg-icon-3">
              <InlineSVG icon="bin" />
            </span>
          </button>
          {{ item.question }}
        </template>
        <template v-slot:cell-action=""> </template>
      </Datatable>
    </div>
  </div>
  <CreateModal></CreateModal>
  <EditModal></EditModal>
</template>

<script>
import { defineComponent, onMounted, ref, computed, watchEffect } from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import CreateModal from "@/views/settings/anesthetic-questions/CreateAnestheticQuestion.vue";
import EditModal from "@/views/settings/anesthetic-questions/EditAnestheticQuestion.vue";
import { Modal } from "bootstrap";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import Swal from "sweetalert2/dist/sweetalert2.js";
import SettingsButton from "@/components/SettingsButton.vue";
export default defineComponent({
  name: "anesthetic-questions",

  components: {
    Datatable,
    CreateModal,
    EditModal,
    SettingsButton,
  },

  setup() {
    const store = useStore();
    const tableHeader = ref([
      {
        name: "Question",
        key: "question",
        sortable: true,
      },
      {
        name: "",
        key: "action",
      },
    ]);
    const tableData = ref([]);
    const anestheticQuestions = computed(
      () => store.getters.getAneQuestionList
    );

    const handleEdit = (item) => {
      store.commit(Mutations.SET_ANESTHETIST_QUES.SELECT, item);
      const modal = new Modal(
        document.getElementById("modal_edit_anesthetic_question")
      );
      modal.show();
    };

    const handleDelete = (id) => {
      store
        .dispatch(Actions.ANESTHETIST_QUES.DELETE, id)
        .then(() => {
          store.dispatch(Actions.ANESTHETIST_QUES.LIST);
          Swal.fire({
            text: "Successfully Deleted!",
            icon: "success",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
              confirmButton: "btn btn-primary",
            },
          });
        })
        .catch(({ response }) => {
          console.log(response.data.error);
        });
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Anesthetic Questions", ["Settings"]);
      store.dispatch(Actions.ANESTHETIST_QUES.LIST);
      tableData.value = anestheticQuestions;
    });

    watchEffect(() => {
      tableData.value = anestheticQuestions;
    });

    return { tableHeader, tableData, handleEdit, handleDelete };
  },
});
</script>
