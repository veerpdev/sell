<template>
  <ModalWrapper
    title="Search Specialists"
    modalId="assign_specialist"
    :updateRef="updateRef"
  >
    <el-form class="w-100" ref="formRef_search_specialist">
      <!--begin::Row-->
      <div class="row g-8">
        <!--begin::Col-->
        <div class="col-lg-4">
          <label class="fs-6 form-label fw-bolder text-dark">First Name</label>
          <el-form-item prop="first_name">
            <el-input
              type="text"
              v-model="filter.first_name"
              placeholder="First Name"
            />
          </el-form-item>
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-lg-4">
          <label class="fs-6 form-label fw-bolder text-dark">Last Name</label>
          <el-form-item prop="last_name">
            <el-input
              type="text"
              v-model="filter.last_name"
              placeholder="Last Name"
            />
          </el-form-item>
        </div>
        <!--begin::Col-->
        <div class="col-lg-4">
          <label class="fs-6 form-label fw-bolder text-dark"
            >Date of Birth</label
          >
          <el-form-item prop="date">
            <el-date-picker
              class="w-100"
              v-model="filter.date_of_birth"
              format="DD/MM/YYYY"
              placeholder="01/01/1990"
            />
          </el-form-item>

          <div class="d-flex align-items-center justify-content-end mt-5">
            <button
              type="submit"
              class="btn btn-primary me-5 w-50"
              :disabled="
                filter.first_name.length < 2 &&
                filter.last_name.length < 2 &&
                filter.date_of_birth.length < 2
              "
              @click.prevent="searchSpecialist"
            >
              SEARCH
            </button>
            <button
              type="submit"
              class="btn btn-light-primary w-50"
              :disabled="
                filter.first_name.length === 0 &&
                filter.last_name.length === 0 &&
                filter.date_of_birth.length === 0
              "
              @click="clearFilters"
            >
              CLEAR
            </button>
          </div>
        </div>
        <!--end::Col-->
      </div>
      <!--end::Row-->
    </el-form>
    <div class="row g-8">
      <Datatable
        :table-header="tableHeader"
        :table-data="tableData"
        :rows-per-page="5"
        :key="tableKey"
        :loading="loading"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-full_name="{ row: item }">
          <span class="text-dark fw-bolder text-hover-primary mb-1 fs-6">
            <button
              @click="handleAssign(item)"
              class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
            >
              <span class="svg-icon svg-icon-3">
                <em class="bi bi-check-circle"></em>
              </span>
            </button>
            {{ item.first_name }} {{ item.last_name }}
          </span>
        </template>
        <template v-slot:cell-dob="{ row: item }">
          <span class="text-dark fw-bolder text-hover-primary mb-1 fs-6">
            {{ new Date(item.date_of_birth).toLocaleDateString("en-AU") }}
          </span>
        </template>
        <template v-slot:cell-contact_number="{ row: item }">
          {{ item.mobile_number }}
        </template>
      </Datatable>
    </div>
  </ModalWrapper>
</template>

<script>
import { defineComponent, ref, reactive, watch, computed } from "vue";
import { useStore } from "vuex";
import { DocumentActions } from "@/store/enums/StoreDocumentEnums";
import { hideModal } from "@/core/helpers/dom";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import { Actions } from "@/store/enums/StoreEnums";

export default defineComponent({
  components: {
    Datatable,
  },
  name: "assign-specialist-modal",
  props: {
    document: { type: Object, required: true },
    handleSetSelectedDocument: { type: Function, required: true },
  },
  setup(props) {
    const store = useStore();
    const list = computed(() => store.getters.getSearchSpecialistList);
    const documentId = computed(() => props.document.id);
    const documentType = computed(() => props.document.document_type);
    const documentName = computed(() => props.document.document_name);
    const loading = ref(false);
    const assignSpecialistModalRef = ref(null);
    const filter = reactive({
      first_name: "",
      last_name: "",
      date_of_birth: "",
    });

    const tableHeader = ref([
      {
        name: "Full Name",
        key: "full_name",
        sortable: true,
        searchable: true,
      },
      {
        name: "Date of Birth",
        key: "dob",
        sortable: true,
        searchable: true,
      },
      {
        name: "Contact Number",
        key: "contact_number",
        sortable: true,
        searchable: true,
      },
    ]);

    const tableData = ref([]);
    const tableKey = ref(0);
    const renderTable = () => tableKey.value++;
    const clearFilters = () => {
      filter.first_name = "";
      filter.last_name = "";
      filter.date_of_birth = "";
      renderTable();
    };

    const searchSpecialist = () => {
      loading.value = true;
      store
        .dispatch(Actions.SPECIALIST.SEARCH.LIST, {
          role_id: 5,
          first_name: filter.first_name,
          last_name: filter.last_name,
          date_of_birth: filter.date_of_birth,
        })
        .finally(() => {
          loading.value = false;
          renderTable();
        });
    };

    const updateRef = (_ref) => {
      assignSpecialistModalRef.value = _ref;
    };

    const handleAssign = (specialist_id) => {
      store
        .dispatch(DocumentActions.UPDATE, {
          specialist_id: specialist_id.id,
          document_id: documentId.value,
          document_type: documentType.value,
          document_name: documentName.value,
        })
        .then(() => {
          clearFilters();
          tableData.value = [];
          renderTable();
          store
            .dispatch(DocumentActions.LIST, {
              is_missing_information: 1,
              origin: "RECEIVED",
            })
            .then(() => {
              setTimeout(() => {
                props.handleSetSelectedDocument("SPECIALIST");
                hideModal(assignSpecialistModalRef.value);
              }, 200);
            });
        });
    };

    watch(list, () => {
      tableData.value = list.value;
      renderTable();
    });

    return {
      filter,
      searchSpecialist,
      clearFilters,
      assignSpecialistModalRef,
      updateRef,
      tableKey,
      tableHeader,
      tableData,
      loading,
      handleAssign,
    };
  },
});
</script>
