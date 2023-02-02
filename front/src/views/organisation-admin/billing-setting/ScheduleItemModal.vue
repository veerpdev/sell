<template>
  <ModalWrapper
    :title="`${isAdd ? 'Add' : 'Edit'} ${isMbs ? 'MBS' : 'Custom'} Item`"
    modalId="schedule_item"
    modalRef="scheduleItemModalRef"
    :is-static="true"
  >
    <!--begin::Form-->
    <el-form
      @submit.prevent="submit()"
      :model="formData"
      :rules="rules"
      :loading="loading"
      ref="formRef"
    >
      <!--begin::Scroll-->
      <div>
        <div v-if="isMbs && !isAdd" class="d-flex gap-6 px-6 mb-4">
          <InfoSection heading="MBS Code">
            {{ scheduleItem?.mbs_item_code }}
          </InfoSection>

          <InfoSection heading="Name">
            {{ scheduleItem?.name }}
          </InfoSection>
        </div>

        <InputWrapper
          v-if="isMbs && isAdd"
          required
          class="fill-out"
          label="MBS Item"
          prop="mbs_item_code_search"
        >
          <div
            class="d-flex flex-column flex-md-row align-items-center w-100 gap-3"
          >
            <div class="d-flex flex-column flex-md-row gap-3 flex-grow-1 w-100">
              <el-input
                class="w-100 w-md-50"
                type="text"
                v-model="mbsItemSearch.item_number"
                placeholder="Search by MBS item number"
                :disabled="mbsItemsLoading"
              />

              <el-input
                class="w-100 w-md-50"
                type="text"
                v-model="mbsItemSearch.description"
                placeholder="Search by MBS description"
                :disabled="mbsItemsLoading"
              />
            </div>

            <button
              :data-kt-indicator="mbsItemsLoading ? 'on' : null"
              class="btn btn-md btn-primary text-nowrap"
              :disabled="
                mbsItemsLoading ||
                (!mbsItemSearch.item_number && !mbsItemSearch.description)
              "
              @click="handleMbsItemSearch"
            >
              <span v-if="!mbsItemsLoading" class="indicator-label">
                Search
              </span>

              <span v-if="mbsItemsLoading" class="indicator-progress">
                Searching...
                <span
                  class="spinner-border spinner-border-sm align-middle ms-2"
                ></span>
              </span>
            </button>
          </div>

          <el-select
            class="col-12 mt-3"
            placeholder="Select MBS item"
            props="mbs_item_code"
            filterable
            remote
            reserve-keyword
            :remote-method="filterMbsItemsHandle"
            :loading="loading"
            :disabled="mbsItemsSearchDataFiltered.length === 0"
            v-model="formData.mbs_item_code"
          >
            <el-option
              v-for="(item, idx) in mbsItemsSearchDataFiltered"
              :key="idx"
              :value="item.mbs_item_number"
              :label="item.label_name"
            />
          </el-select>
        </InputWrapper>

        <template v-if="!isMbs">
          <InputWrapper required class="fill-out" label="Item Name" prop="name">
            <el-input
              type="text"
              v-model="formData.name"
              placeholder="Enter item name..."
              :disabled="loading"
            />
          </InputWrapper>

          <InputWrapper class="fill-out" label="Item Description" prop="name">
            <el-input
              type="text"
              v-model="formData.description"
              placeholder="Enter item description..."
              :disabled="loading"
            />
          </InputWrapper>

          <InputWrapper class="fill-out" label="Internal Code" prop="name">
            <el-input
              type="text"
              v-model="formData.internal_code"
              placeholder="Enter internal code..."
              :disabled="loading"
            />
          </InputWrapper>
        </template>

        <InputWrapper
          required
          class="fill-out"
          label="Out of Pocket Amount"
          prop="amount"
        >
          <CurrencyInput v-model="formData.amount" placeholder="Amount" />
        </InputWrapper>
      </div>
      <!--end::Scroll-->

      <!--begin::Modal footer-->
      <div class="d-flex justify-content-end">
        <!--begin::Button-->
        <button
          v-if="!isAdd"
          :data-kt-indicator="loading ? 'on' : null"
          class="btn btn-lg btn-danger me-3"
          @click.prevent="handleDelete"
        >
          Delete
        </button>
        <!--end::Button-->

        <!--begin::Button-->
        <button
          type="reset"
          data-bs-dismiss="modal"
          id="kt_modal_schedule_item_cancel"
          class="btn btn-light me-3"
        >
          Cancel
        </button>
        <!--end::Button-->

        <!--begin::Button-->
        <button
          :data-kt-indicator="loading ? 'on' : null"
          class="btn btn-lg btn-primary"
          type="submit"
        >
          <span v-if="!loading" class="indicator-label">
            {{ isAdd ? "Add" : "Update" }}
          </span>
          <span v-if="loading" class="indicator-progress">
            Please wait...
            <span
              class="spinner-border spinner-border-sm align-middle ms-2"
            ></span>
          </span>
        </button>
        <!--end::Button-->
      </div>
      <!--end::Modal footer-->
    </el-form>
  </ModalWrapper>
</template>

<script lang="ts">
import { defineComponent, ref, watch, computed } from "vue";
import { useStore } from "vuex";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { Actions } from "@/store/enums/StoreEnums";
import { MbsItem } from "@/store/modules/MbsModule";
import InfoSection from "@/components/presets/GeneralElements/InfoSection.vue";

export default defineComponent({
  name: "schedule-item-modal",
  components: {
    InfoSection,
  },
  props: {
    item: { type: Object },
  },
  setup(props, { emit }) {
    const store = useStore();
    const formRef = ref();
    const scheduleItemModalRef = ref();
    const loading = ref(false);
    const mbsItemsLoading = ref(false);
    const scheduleItem = computed(() => props.item);
    const isAdd = computed(() => scheduleItem.value?.mode === "add");
    const isMbs = computed(() => scheduleItem.value?.type === "mbs");
    const mbsItemsSearchData = ref<Array<MbsItem>>([]);
    const mbsItemsSearchDataFiltered = ref<Array<MbsItem>>([]);

    const mbsItemSearch = ref({
      item_number: null,
      description: null,
    });

    const formData = ref({
      name: "",
      description: "",
      amount: 0,
      mbs_item_code: "",
      internal_code: "",
    });

    const rules = ref({
      name: [
        {
          required: true,
          message: "Name cannnot be blank",
          trigger: "change",
        },
      ],
      amount: [
        {
          required: true,
          message: "Amount cannot be blank",
          trigger: "change",
        },
      ],
    });

    const filterMbsItemsHandle = (query: string) => {
      if (query) {
        loading.value = true;
        setTimeout(() => {
          loading.value = false;
          mbsItemsSearchDataFiltered.value = mbsItemsSearchData.value.filter(
            (item) => {
              return (
                item.mbs_item_number.toString().includes(query) ||
                item.description.includes(query)
              );
            }
          );
        }, 200);
      } else {
        mbsItemsSearchDataFiltered.value = [];
      }
    };

    const handleMbsItemSearch = () => {
      mbsItemsLoading.value = true;
      store
        .dispatch(Actions.MBS.LIST, mbsItemSearch.value)
        .then(() => {
          mbsItemsSearchData.value = store.getters.mbsItems.items;
          mbsItemsSearchDataFiltered.value = store.getters.mbsItems.items;
        })
        .finally(() => {
          mbsItemsLoading.value = false;
        });
    };

    const submit = () => {
      if (formRef.value) {
        formRef.value.validate((valid) => {
          if (valid) {
            let data;
            let action = Actions.SCHEDULE_ITEM.CREATE;

            if (isMbs.value) {
              const mbsItem = mbsItemsSearchDataFiltered.value.find(
                (item) =>
                  item.mbs_item_number.toString() ==
                  formData.value.mbs_item_code
              );
              data = {
                name: mbsItem?.name,
                description: mbsItem?.description,
                mbs_item_code: formData.value.mbs_item_code,
                amount: formData.value.amount,
              };
            } else {
              data = {
                ...formData.value,
              };
            }

            data.amount = data.amount * 100;

            if (!isAdd.value) {
              data.id = scheduleItem.value?.id;
              action = Actions.SCHEDULE_ITEM.UPDATE;
            }

            loading.value = true;
            store
              .dispatch(action, data)
              .then(() => {
                store.dispatch(Actions.SCHEDULE_ITEM.LIST);
                Swal.fire({
                  text: "Successfully Saved!",
                  icon: "success",
                  buttonsStyling: false,
                  confirmButtonText: "Ok, got it!",
                  customClass: {
                    confirmButton: "btn btn-primary",
                  },
                }).then(() => {
                  formRef.value.resetFields();
                  emit("closeModal");
                });
              })
              .catch((error) => {
                Swal.fire({
                  text: error,
                  icon: "error",
                  buttonsStyling: false,
                  confirmButtonText: "Ok, got it!",
                  customClass: {
                    confirmButton: "btn btn-primary",
                  },
                });
              })
              .finally(() => {
                loading.value = false;
              });
          }
        });
      }
    };

    const handleDelete = () => {
      Swal.fire({
        text: `Are you sure you want to delete this item?`,
        icon: "question",
        buttonsStyling: false,
        confirmButtonText: "Yes",
        showCancelButton: true,
        cancelButtonText: "No",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-secondary",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          store
            .dispatch(Actions.SCHEDULE_ITEM.DELETE, scheduleItem.value?.id)
            .then(() => {
              store.dispatch(Actions.SCHEDULE_ITEM.LIST);
              Swal.fire({
                text: "Successfully Delete!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                  confirmButton: "btn btn-primary",
                },
              }).then(() => {
                formRef.value.resetFields();
                emit("closeModal");
              });
            });
        }
      });
    };

    watch(scheduleItem, () => {
      formData.value.mbs_item_code = scheduleItem.value?.mbs_item_code;
      formData.value.internal_code = scheduleItem.value?.internal_code;
      formData.value.name = scheduleItem.value?.name;
      formData.value.description = scheduleItem.value?.description;
      formData.value.amount = scheduleItem.value?.amount / 100;
    });

    return {
      formData,
      rules,
      submit,
      formRef,
      loading,
      scheduleItemModalRef,
      scheduleItem,
      isAdd,
      isMbs,
      mbsItemsLoading,
      mbsItemsSearchData,
      mbsItemsSearchDataFiltered,
      mbsItemSearch,
      filterMbsItemsHandle,
      handleMbsItemSearch,
      handleDelete,
    };
  },
});
</script>
