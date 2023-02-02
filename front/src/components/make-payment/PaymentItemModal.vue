<template>
  <ModalWrapper
    :title="modalTitle"
    modalId="payment_item"
    modalRef="paymentItemModalRef"
    :is-static="true"
  >
    <el-form
      @submit.prevent
      :model="formData"
      :rules="rules"
      ref="paymentItemModalFormRef"
    >
      <div class="row justify-content-md-center">
        <InputWrapper label="Item" prop="schedule_item_id">
          <el-select
            class="w-100"
            v-model="formData.schedule_item_id"
            placeholder="Select Item"
            :disabled="mode === 'edit'"
          >
            <el-option
              v-for="(item, index) in availableItems"
              :key="`item-modal-select-option-${index}`"
              :value="item.id"
              :label="getItemName(item)"
            />
          </el-select>
        </InputWrapper>

        <InputWrapper v-if="canEditItem" label="Price" prop="price">
          <CurrencyInput v-model="formData.price" placeholder="Enter price" />
        </InputWrapper>

        <div v-if="!canEditItem" class="px-6 pb-6">
          <InfoSection heading="Price">
            {{ convertToCurrency(formData.price) }}
          </InfoSection>
        </div>
      </div>
    </el-form>

    <div class="d-flex justify-content-end">
      <button
        v-if="!canEditItem"
        class="btn btn-lg btn-secondary me-2"
        @click="openPinConfirmModal"
      >
        {{ mode === "add" ? "Edit Price" : "Enable Editing" }}
      </button>

      <button
        v-if="mode === 'edit'"
        class="btn btn-lg btn-danger me-2"
        :disabled="!canEditItem"
        @click="handleDelete"
      >
        Delete
      </button>

      <button
        class="btn btn-lg btn-primary me-2"
        :disabled="!formData.schedule_item_id"
        @click="submitItem"
      >
        {{ mode === "add" ? "Add Item" : "Update" }}
      </button>

      <button
        class="btn btn-lg btn-secondary"
        data-bs-dismiss="modal"
        type="submit"
      >
        Cancel
      </button>
    </div>

    <VerifyPinModal
      customId="verify_org_pin_item_modal"
      v-on:verified="enableEditPrice"
      v-on:closeModal="closePinConfirmModal"
    />
  </ModalWrapper>
</template>

<script>
import {
  defineComponent,
  ref,
  computed,
  onMounted,
  watchEffect,
  watch,
} from "vue";
import { useStore } from "vuex";
import { Actions } from "@/store/enums/StoreEnums";
import { convertToCurrency } from "@/core/data/billing";
import Swal from "sweetalert2/dist/sweetalert2.js";
import moment from "moment";
import { Modal } from "bootstrap";
import VerifyPinModal from "@/components/organisation-admins/VerifyPinModal.vue";

export default defineComponent({
  name: "payment-item-modal",
  components: {
    VerifyPinModal,
  },
  props: {
    item: { type: Object },
  },
  emits: ["submitItem", "closeModal", "deleteItem"],
  setup(props, { emit }) {
    const store = useStore();
    const item = computed(() => props.item);
    const paymentItemModalRef = ref(null);
    const paymentItemModalFormRef = ref(null);
    const formData = ref({
      schedule_item_id: null,
      price: null,
    });
    const loading = ref(false);
    const mode = ref("add");
    const category = ref("");
    const scheduleItems = computed(() => store.getters.scheduleItemList);
    const canEditItem = ref(false);
    const verifyAuthorizationPinModal = ref();
    const authorizedBy = ref(null);

    const rules = ref({
      billing_type: [
        {
          required: true,
          message: "Must select claim source type",
          trigger: "change",
        },
      ],
      member_number: [
        {
          required: true,
          message: "Member number cannot be blank",
          trigger: "change",
        },
      ],
      member_reference_number: [
        {
          required: false,
          message: "Member reference number cannot be blank",
          trigger: "change",
        },
      ],
    });

    const enableEditPrice = (authorizingUser) => {
      console.log("User", authorizingUser);
      canEditItem.value = true;
      authorizedBy.value = authorizingUser;
      closePinConfirmModal();
    };

    const openPinConfirmModal = () => {
      if (!verifyAuthorizationPinModal.value) {
        verifyAuthorizationPinModal.value = new Modal(
          document.getElementById("modal_verify_org_pin_item_modal")
        );
      }

      verifyAuthorizationPinModal.value.show();
    };

    const closePinConfirmModal = () => {
      verifyAuthorizationPinModal.value.hide();
    };

    const closeModal = () => {
      emit("closeModal");
    };

    const resetForm = () => {
      paymentItemModalFormRef.value.resetFields();
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
          emit("deleteItem", authorizedBy.value);
          closeModal();
        }
      });
    };

    const submitItem = () => {
      const selectedItem = scheduleItems.value.find(
        (item) => item.id === formData.value.schedule_item_id
      );
      const data = {
        ...selectedItem,
        price: formData.value.price,
      };

      if (authorizedBy.value) {
        data.authorized_by = authorizedBy.value;
      }
      console.log(data);
      emit("submitItem", data);
    };

    const getItemName = (item) => {
      const isMbsItem = item.mbs_item_code ? true : false;

      if (isMbsItem) {
        return `${item.mbs_item_code} - ${item.name}`;
      }

      let name = [];
      if (item.internal_code) {
        name.push(item.internal_code);
      }

      name.push(item.name);

      return name.join(" - ");
    };

    const modalTitle = computed(() => {
      let title = mode.value === "add" ? "Add " : "Edit ";

      switch (category.value) {
        case "procedures":
          title += "MBS Procedure";
          break;
        case "extra_items":
          title += "Extra MBS Item";
          break;
        case "admin_items":
          title += "Non-MBS Item";
          break;
      }

      return title;
    });

    const availableItems = computed(() => {
      const isMbs = category.value !== "admin_items";

      return scheduleItems.value.filter((item) => {
        if (isMbs) {
          return item.mbs_item_code !== null;
        }

        return item.mbs_item_code === null;
      });
    });

    watch(
      () => item,
      () => {
        resetForm();
        formData.value.schedule_item_id = item.value.item?.id;
        formData.value.price = item.value.item?.price
          ? item.value.item?.price / 100
          : 0;
        mode.value = item.value.mode;
        category.value = item.value.category;
      },
      { deep: true }
    );

    watch(
      () => formData.value.schedule_item_id,
      (value) => {
        if (mode.value === "add" && value) {
          const selectedItem = scheduleItems.value.find(
            (item) => item.id === value
          );

          // amounts are stored in cents, but we want the value
          // in the textbox to be dollars
          formData.value.price = selectedItem?.amount / 100;
        }
      }
    );

    onMounted(() => {
      store.dispatch(Actions.SCHEDULE_ITEM.LIST);

      const modal = document.getElementById("modal_payment_item");
      modal.addEventListener("shown.bs.modal", function () {
        canEditItem.value = false;
      });
    });

    return {
      store,
      paymentItemModalRef,
      paymentItemModalFormRef,
      formData,
      loading,
      rules,
      closeModal,
      modalTitle,
      mode,
      availableItems,
      handleDelete,
      submitItem,
      openPinConfirmModal,
      canEditItem,
      enableEditPrice,
      closePinConfirmModal,
      getItemName,
      convertToCurrency,
    };
  },
});
</script>
