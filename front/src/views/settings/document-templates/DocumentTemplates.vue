<template>
  <SettingsButton />
  <div class="card w-75 mx-auto">
    <div class="card-header p-6">
      <!-- <div class="card-title col"></div> -->
      <div class="d-flex align-items-center">
        <!-- begin::Search by title -->
        <div class="d-flex align-items-center position-relative my-1">
          <span class="svg-icon svg-icon-1 position-absolute ms-6">
            <InlineSVG icon="search" />
          </span>
          <input
            type="text"
            data-kt-subscription-table-filter="search"
            class="form-control form-control-solid w-250px ps-14"
            placeholder="Search Title"
            v-model="filterAndSort.searchText"
          />
        </div>
        <!-- end::Search by title -->
        <!-- begin::Filter by type -->
        <el-select
          class="d-flex align-items-center w-50 mx-5"
          placeholder="Select Template Type"
          v-model="templateTypeFilter"
        >
          <el-option value="ALL" label="ALL TEMPLATE TYPES">
            ALL TEMPLATE TYPES
          </el-option>
          <template
            v-for="docType in patientDocumentTemplateTypes"
            :key="docType.value"
          >
            <el-option :value="docType.value" :label="docType.label">
              <inline-svg class="me-5" :src="docType.icon" />
              {{ docType.label }}
            </el-option>
          </template>
        </el-select>
        <!-- end::Filter by type -->
      </div>
      <!--begin::Add button-->
      <div class="card-toolbar text-center col-sm-2">
        <button
          type="button"
          class="btn btn-light-primary ms-auto text-nowrap"
          @click="handleAdd()"
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
        v-if="tableData != undefined"
        :table-header="tableHeader"
        :table-data="tableData"
        :key="tableKey"
        :rows-per-page="20"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-title="{ row: item }">
          <div class="d-flex align-items-center">
            {{ item.title }}
          </div>
        </template>

        <template v-slot:cell-type="{ row: item }">
          <div class="d-flex align-items-center">
            {{ item.type }}
          </div>
        </template>

        <template v-slot:cell-action="{ row: item }">
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
            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
          >
            <span class="svg-icon svg-icon-3">
              <InlineSVG icon="bin" />
            </span>
          </button>
        </template>
      </Datatable>
    </div>
  </div>
  <CreateDocumentTemplate />
</template>

<script lang="ts">
import {
  defineComponent,
  onMounted,
  ref,
  computed,
  watchEffect,
  reactive,
  watch,
} from "vue";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import CreateDocumentTemplate from "@/views/settings/document-templates/CreateDocumentTemplate.vue";
import { Modal } from "bootstrap";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import SettingsButton from "@/components/SettingsButton.vue";
import IDocumentTemplate from "@/store/interfaces/IDocumentTemplate";
import patientDocumentTemplateTypes from "@/core/data/patient-document-template-types";

export default defineComponent({
  name: "document-templates",

  components: {
    Datatable,
    CreateDocumentTemplate,
    SettingsButton,
  },

  setup() {
    const store = useStore();
    const tableHeader = ref([
      {
        name: "Title",
        key: "title",
        sortable: true,
      },
      {
        name: "Type",
        key: "type",
        sortable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);

    const tableData = ref<IDocumentTemplate[]>([]);
    const documentTemplates = computed(
      () => store.getters.getDocumentTemplateList
    );
    const filterAndSort = reactive({
      searchText: "",
    });
    const filteredData = ref<IDocumentTemplate[]>([]);
    const templateTypeFilter = ref("ALL");
    const tableKey = ref(0);

    const renderTable = () => tableKey.value++;

    const handleAdd = () => {
      const new_item = {
        id: 0,
        title: "",
        type: "",
        sections: [],
      };

      store.commit(Mutations.SET_DOCUMENT_TEMPLATES.SELECT, {
        template: new_item,
        appointment: null,
        headerFooter: null,
      });
      const modal = new Modal(
        document.getElementById("modal_add_document_template")
      );
      modal.show();
    };

    const applyFilterAndSort = () => {
      let filterType =
        templateTypeFilter.value != "ALL" ? templateTypeFilter.value : null;
      if (filterAndSort.searchText != "") {
        filteredData.value = documentTemplates.value.filter((item) => {
          if (
            item.title
              .toLowerCase()
              .search(filterAndSort.searchText.toLowerCase()) >= 0
          ) {
            return true;
          }

          return false;
        });
      } else {
        filteredData.value = documentTemplates.value;
      }

      if (filterType) {
        filteredData.value = filteredData.value.filter((item) => {
          if (item.type.toLowerCase() === filterType?.toLowerCase())
            return true;
          return false;
        });
      }
      tableData.value = filteredData.value;
    };

    const handleEdit = (item) => {
      store.commit(Mutations.SET_DOCUMENT_TEMPLATES.SELECT, {
        template: item,
        appointment: null,
        headerFooter: null,
      });
      const modal = new Modal(
        document.getElementById("modal_add_document_template")
      );
      modal.show();
    };

    const handleDelete = (id) => {
      store
        .dispatch(Actions.DOCUMENT_TEMPLATES.DELETE, id)
        .then(() => {
          store.dispatch(Actions.DOCUMENT_TEMPLATES.LIST);
        })
        .catch((response) => {
          console.log(response);
        });
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Document Templates", ["Settings"]);
      store.dispatch(Actions.DOCUMENT_TEMPLATES.LIST);
    });

    watch([filterAndSort, templateTypeFilter], () => {
      applyFilterAndSort();
      renderTable();
    });

    watchEffect(() => {
      applyFilterAndSort();
      renderTable();
    });

    return {
      tableHeader,
      tableData,
      handleAdd,
      handleEdit,
      handleDelete,
      filterAndSort,
      tableKey,
      patientDocumentTemplateTypes,
      templateTypeFilter,
    };
  },
});
</script>
