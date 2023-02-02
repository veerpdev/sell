<template>
  <CardSection heading=" ">
    <template #header-actions>
      <IconButton
        @click="handleAdd"
        label="Add New Bulletin"
        :iconSRC="icons.plus"
      />
    </template>
    <template #default>
      <Datatable
        v-if="tableData"
        :table-header="tableHeader"
        :table-data="tableData"
        :loading="loading"
        :rows-per-page="20"
        :enable-items-per-page-dropdown="true"
      >
        <template v-slot:cell-title="{ row: item }">
          <div class="d-flex align-items-center">
            {{ item.title }}
          </div>
        </template>
        <template v-slot:cell-author="{ row: item }">
          <div class="d-flex align-items-center">
            {{ item.created_by_name }}
          </div>
        </template>
        <template v-slot:cell-date="{ row: item }">
          <div class="d-flex align-items-center">
            {{ new Date(item.created_at).toLocaleDateString("en-AU") }}
          </div>
        </template>
        <template v-slot:cell-action="{ row: item }">
          <div class="d-flex justify-content-end gap-1">
            <IconButton
              @click="handleEdit(item)"
              :iconSRC="icons.pencil"
              tooltip="Edit bulletin"
            />
            <IconButton
              @click="handleDelete(item.id)"
              :iconSRC="icons.bin"
              tooltip="Delete bulletin"
            />
          </div>
        </template>
      </Datatable>
    </template>
  </CardSection>

  <BulletinEditModal></BulletinEditModal>
</template>

<script lang="ts">
import { defineComponent, onMounted, computed, ref, watchEffect } from "vue";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import { HRMActions, HRMMutations } from "@/store/enums/StoreHRMEnums";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import BulletinEditModal from "@/views/HRM/modals/BulletinEditModal.vue";
import { useStore } from "vuex";
import { Modal } from "bootstrap";
import icons from "@/core/data/icons";
import Swal from "sweetalert2/dist/sweetalert2.js";
import IBulletin from "@/store/interfaces/IBulletin";
import CardSection from "@/components/presets/GeneralElements/CardSection.vue";

export default defineComponent({
  name: "bulletin-manage",
  components: {
    Datatable,
    BulletinEditModal,
    CardSection,
  },
  setup() {
    const store = useStore();
    const loading = ref<boolean>(true);
    const tableData = ref();
    const bulletins = computed<IBulletin[]>(
      () => store.getters.getBulletinList
    );

    const tableHeader = ref([
      {
        name: "Title",
        key: "title",
        sortable: true,
      },
      {
        name: "Author",
        key: "author",
        sortable: true,
      },
      {
        name: "Date",
        key: "date",
        sortable: true,
      },
      {
        name: "Action",
        key: "action",
      },
    ]);

    watchEffect(() => {
      console.log(bulletins);
      tableData.value = bulletins;
    });

    onMounted(() => {
      loading.value = true;
      setCurrentPageBreadcrumbs("HRM", ["Manage Bulletins"]);
      store.dispatch(HRMActions.BULLETIN.LIST).then(() => {
        loading.value = false;
      });
    });

    const handleAdd = () => {
      const new_item = {
        title: "",
        body: "",
        created_by_name: "",
        created_at: new Date(),
        _title: "Create Bulletin",
        _button: "Save",
        _submit: HRMActions.BULLETIN.CREATE,
        _submit_text: "Successfully Created!",
      };

      store.commit(HRMMutations.BULLETIN.SET_SELECT, new_item);
      const modal = new Modal(document.getElementById("modal_edit_bulletin"));
      modal.show();
    };

    const handleEdit = (item) => {
      item._title = "Edit Bulletin";
      item._button = "Update";
      item._submit = HRMActions.BULLETIN.UPDATE;
      item._submit_text = "Successfully Updated!";

      store.commit(HRMMutations.BULLETIN.SET_SELECT, item);
      const modal = new Modal(document.getElementById("modal_edit_bulletin"));
      modal.show();
    };

    const handleDelete = (id) => {
      //delete templates
      store
        .dispatch(HRMActions.BULLETIN.DELETE, id)
        .then(() => {
          store.dispatch(HRMActions.BULLETIN.LIST);
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
          console.log(response);
        });
    };

    return {
      icons,
      bulletins,
      loading,
      tableData,
      tableHeader,
      handleAdd,
      handleEdit,
      handleDelete,
    };
  },
});
</script>
