<template>
  <CardSection>
    <template #default>
      <div class="d-flex flex-row justify-content-between">
        <div class="d-flex align-items-center position-relative my-1">
          <span class="svg-icon svg-icon-1 position-absolute ms-6">
            <InlineSVG icon="search" />
          </span>
          <input
            type="text"
            data-kt-subscription-table-filter="search"
            class="form-control form-control-solid w-250px ps-14"
            placeholder="Search Auto Texts"
            v-model="filterAndSort.searchText"
          />
        </div>

        <IconButton
          @click="handleCreate"
          label="Add New Auto Text"
          :iconSRC="icons.plus"
        />
      </div>

      <Datatable
        v-if="tableData != undefined"
        :table-header="tableHeader"
        :table-data="tableData"
        :loading="loading"
        :rows-per-page="10"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-text="{ row: item }">
          {{ item.text }}
        </template>
        <template v-slot:cell-suggested_codes="{ row: item }">
          {{ item.suggested_codes }}
        </template>
        <template v-slot:cell-report_types="{ row: item }">
          {{ item.report_types }}
        </template>
        <template v-slot:cell-action="{ row: item }">
          <div class="d-flex justify-content-end gap-1">
            <IconButton
              @click="handleEdit(item)"
              :iconSRC="icons.pencil"
              tooltip="Edit Auto Text"
            />
            <IconButton
              @click="handleDelete(item)"
              :iconSRC="icons.bin"
              tooltip="Delete Auto Text"
            />
          </div>
        </template>
      </Datatable>
    </template>
  </CardSection>
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
import { useRouter } from "vue-router";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions, Mutations } from "@/store/enums/StoreEnums";
import icons from "@/core/data/icons";
import IAutoText from "@/store/interfaces/IAutoText";
export default defineComponent({
  name: "auto-text-settings",

  components: {
    Datatable,
  },

  setup() {
    const store = useStore();
    const router = useRouter();
    const loading = ref<boolean>(false);
    const filterAndSort = reactive({
      searchText: "",
    });
    const user = computed(() => store.getters.userProfile);
    const list = computed<IAutoText[]>(() => store.getters.autoTextsList);
    const tableHeader = ref([
      {
        name: "Text",
        key: "text",
        sortable: true,
        searchable: true,
      },
      {
        name: "Suggested Codes",
        key: "suggested_codes",
        sortable: true,
        searchable: true,
      },
      {
        name: "Report Types",
        key: "report_types",
        sortable: true,
        searchable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);
    const tableData = ref<IAutoText[]>();
    const filteredData = ref<IAutoText[]>();

    const handleCreate = () => {
      router.push({ name: "auto-text-create" });
    };

    const handleEdit = (item) => {
      store.commit(Mutations.SET_AUTO_TEXTS.SELECT, item);
      router.push({ name: "auto-text-edit", params: { id: item.id } });
    };

    const handleDelete = (item) => {
      Swal.fire({
        icon: "info",
        text: "Are you sure to delete this item?",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonText: "Delete",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light-primary",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          store.dispatch(Actions.AUTO_TEXT.DELETE, item.id).then(() => {
            store.dispatch(Actions.AUTO_TEXT.LIST, { user_id: user.value.id });
          });
        }
      });
    };

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("Auto Text Settings", ["Profile"]);
      store
        .dispatch(Actions.AUTO_TEXT.LIST, { user_id: user.value.id })
        .then(() => {
          tableData.value = list.value;
          loading.value = false;
        });
    });

    const applyFilterAndSort = () => {
      if (filterAndSort.searchText != "") {
        filteredData.value = list.value.filter((item) => {
          if (
            item.text
              .toLowerCase()
              .search(filterAndSort.searchText.toLowerCase()) >= 0
          ) {
            return true;
          }

          return false;
        });
      } else {
        filteredData.value = list.value;
      }
      tableData.value = filteredData.value;
    };

    watchEffect(() => {
      applyFilterAndSort();
    });

    watch(filterAndSort, () => {
      applyFilterAndSort();
    });

    return {
      tableHeader,
      tableData,
      handleEdit,
      handleDelete,
      handleCreate,
      filterAndSort,
      icons,
      loading,
    };
  },
});
</script>
