<template>
  <div v-if="Object.prototype.hasOwnProperty.call(billingData, 'appointment')">
    <CardSection heading="Appointment Details">
      <div class="d-flex flex-column flex-sm-row gap-4">
        <div class="col-sm-6">
          <div class="d-flex flex-column gap-4">
            <label class="text-muted fs-6 fw-bold mb-2 d-block">
              Appointment
            </label>

            <InfoSection heading="Date">
              {{ billingData.appointment?.aus_formatted_date }},
              {{ billingData.appointment?.start_time }}
            </InfoSection>

            <InfoSection heading="Clinic">
              {{ billingData.appointment?.clinic?.name }}
            </InfoSection>
          </div>
        </div>

        <div class="col-sm-6 mt-6 mt-sm-0">
          <div class="d-flex flex-column gap-4">
            <label class="text-muted fs-6 fw-bold mb-2 d-block">
              Patient
            </label>

            <InfoSection heading="Name">
              {{ billingData.appointment.patient_name.full }}
            </InfoSection>

            <InfoSection heading="Contact Number">
              {{ billingData.appointment.patient_details.contact_number }}
            </InfoSection>

            <InfoSection heading="Email">
              {{ billingData.appointment.patient_details.email }}
            </InfoSection>
          </div>
        </div>
      </div>
    </CardSection>

    <div class="card mb-5 mb-xxl-8">
      <div class="card-header pt-5">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold fs-3 mb-1">Payment Details</span>
        </h3>
      </div>
      <div class="card-body pt-3 pb-0">
        <div class="row">
          <div class="col-md-7 mb-4 p-3 pe-5">
            <div
              v-if="billingData.organization?.has_billing ?? false"
              class="fv-row mb-8"
            >
              <div
                class="d-flex justify-content-between align-items-center gap-4"
              >
                <label class="text-muted fs-6 fw-bold">Procedures</label>

                <IconButton
                  label="Add Procedure"
                  @click="handleAddItem('procedures')"
                />
              </div>

              <Datatable
                :table-header="mbsTableHeader"
                :table-data="billingData.charges.procedures"
                :enable-items-per-page-dropdown="false"
                empty-table-text="No items added"
              >
                <template v-slot:cell-mbs_code="{ row: item }">
                  <span
                    :class="{
                      'text-decoration-line-through': item?.deleted_by,
                    }"
                  >
                    {{ item.mbs_item_code }}
                  </span>
                </template>

                <template v-slot:cell-description="{ row: item }">
                  <template v-if="item.deleted_by">
                    <span class="f2-7 fw-bold text-danger">
                      Delete authorized by:
                      {{ item.deleted_by?.full_name }}
                    </span>
                  </template>

                  <template v-else>
                    {{ item.name }}
                  </template>
                </template>

                <template v-slot:cell-price="{ row: item }">
                  {{ convertToCurrency(item.price / 100) }}

                  <template v-if="item.authorized_by && !item?.deleted_by">
                    <br />
                    <span class="f2-7 fw-bold text-muted">
                      Authorized by:
                      {{ item.authorized_by?.full_name }}
                    </span>
                  </template>
                </template>

                <template v-slot:cell-actions="{ row: item }">
                  <button
                    v-if="!item?.deleted_by"
                    type="button"
                    class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                    @click="handleEditItem('procedures', item)"
                  >
                    <span class="svg-icon svg-icon-3">
                      <InlineSVG icon="pencil" />
                    </span>
                  </button>
                </template>
              </Datatable>
            </div>

            <div
              v-if="billingData.organization?.has_billing ?? false"
              class="fv-row mb-8"
            >
              <div
                class="d-flex justify-content-between align-items-center gap-4"
              >
                <label class="text-muted fs-6 fw-bold">Extra Items</label>

                <IconButton
                  label="Add Extra Item"
                  @click="handleAddItem('extra_items')"
                />
              </div>

              <Datatable
                :table-header="mbsTableHeader"
                :table-data="billingData.charges.extra_items"
                :enable-items-per-page-dropdown="false"
                empty-table-text="No items added"
              >
                <template v-slot:cell-mbs_code="{ row: item }">
                  <span
                    :class="{
                      'text-decoration-line-through': item?.deleted_by,
                    }"
                  >
                    {{ item.mbs_item_code }}
                  </span>
                </template>

                <template v-slot:cell-description="{ row: item }">
                  <template v-if="item.deleted_by">
                    <span class="f2-7 fw-bold text-danger">
                      Delete authorized by:
                      {{ item.deleted_by?.full_name }}
                    </span>
                  </template>

                  <template v-else>
                    {{ item.name }}
                  </template>
                </template>

                <template v-slot:cell-price="{ row: item }">
                  {{ convertToCurrency(item.price / 100) }}

                  <template v-if="item.authorized_by && !item?.deleted_by">
                    <br />
                    <span class="f2-7 fw-bold text-muted">
                      Authorized by:
                      {{ item.authorized_by?.full_name }}
                    </span>
                  </template>
                </template>

                <template v-slot:cell-actions="{ row: item }">
                  <button
                    v-if="!item?.deleted_by"
                    type="button"
                    class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                    @click="handleEditItem('extra_items', item)"
                  >
                    <span class="svg-icon svg-icon-3">
                      <InlineSVG icon="pencil" />
                    </span>
                  </button>
                </template>
              </Datatable>
            </div>

            <div class="fv-row mb-8">
              <div
                class="d-flex justify-content-between align-items-center gap-4"
              >
                <label class="text-muted fs-6 fw-bold">Non-MBS Items</label>

                <IconButton
                  label="Add Non-MBS Item"
                  @click="handleAddItem('admin_items')"
                />
              </div>

              <Datatable
                :table-header="tableHeader"
                :table-data="billingData.charges.admin_items"
                :enable-items-per-page-dropdown="false"
                empty-table-text="No items added"
              >
                <template v-slot:cell-name="{ row: item }">
                  <span
                    :class="{
                      'text-decoration-line-through': item?.deleted_by,
                    }"
                  >
                    {{ item.name }}
                  </span>
                </template>

                <template v-slot:cell-description="{ row: item }">
                  <template v-if="item.deleted_by">
                    <span class="f2-7 fw-bold text-danger">
                      Delete authorized by:
                      {{ item.deleted_by?.full_name }}
                    </span>
                  </template>

                  <template v-else>
                    {{ item.description }}
                  </template>
                </template>

                <template v-slot:cell-price="{ row: item }">
                  {{ convertToCurrency(item.price / 100) }}

                  <template v-if="item.authorized_by && !item?.deleted_by">
                    <br />
                    <span class="f2-7 fw-bold text-muted">
                      Authorized by:
                      {{ item.authorized_by?.full_name }}
                    </span>
                  </template>
                </template>

                <template v-slot:cell-actions="{ row: item }">
                  <button
                    v-if="!item?.deleted_by"
                    type="button"
                    class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                    @click="handleEditItem('admin_items', item)"
                  >
                    <span class="svg-icon svg-icon-3">
                      <InlineSVG icon="pencil" />
                    </span>
                  </button>
                </template>
              </Datatable>
            </div>

            <PaymentItemModal
              :item="paymentItemModalData"
              v-on:deleteItem="handleDeleteItem"
              v-on:submitItem="handlePaymentItemModalSubmit"
              v-on:closeModal="handleCloseModal"
            />
          </div>

          <div class="col-md-5 p-3 ps-5 mb-4 border-start border-light">
            <div class="fv-row mb-8">
              <label class="text-muted fs-6 fw-bold mb-2 d-block">
                Charge Type
              </label>
              <el-form>
                <el-form-item prop="charge_type">
                  <el-select
                    class="w-100"
                    v-model="billingData.appointment.charge_type"
                    placeholder="Select Charge Type"
                    disabled
                  >
                    <el-option
                      v-for="type in getChargeTypes(
                        billingData.appointment.patient
                      )"
                      :key="type.value"
                      :value="type.value"
                      :label="type.label"
                      :selected="type.label === 'self-insured'"
                    />
                  </el-select>
                </el-form-item>
              </el-form>
            </div>

            <div class="fv-row mb-8">
              <div class="fs-6 fw-bold mt-2 d-flex flex-column gap-2">
                <InfoSection heading="Total Payable Amount">
                  {{ convertToCurrency(procedurePrice / 100) }}
                </InfoSection>

                <InfoSection heading="Amount Paid">
                  {{ convertToCurrency(amountPaid / 100) }}
                </InfoSection>

                <InfoSection heading="Amount Outstanding">
                  {{ convertToCurrency(amountOutstanding / 100) }}
                </InfoSection>

                <button
                  class="btn btn-light-primary w-100 mt-6"
                  :disabled="sendingAppointmentInvoice"
                  :data-kt-indicator="sendingAppointmentInvoice ? 'on' : null"
                  @click="sendAppointmentInvoice"
                >
                  <span
                    v-if="!sendingAppointmentInvoice"
                    class="indicator-label"
                  >
                    Send Appointment Invoice
                  </span>

                  <span
                    v-if="sendingAppointmentInvoice"
                    class="indicator-progress"
                  >
                    Sending...
                    <span
                      class="spinner-border spinner-border-sm align-middle ms-2"
                    ></span>
                  </span>
                </button>

                <button
                  class="btn btn-light-primary w-100 mt-2"
                  :disabled="viewingAppointmentInvoice"
                  :data-kt-indicator="viewingAppointmentInvoice ? 'on' : null"
                  @click="viewAppointmentInvoice"
                >
                  <span
                    v-if="!viewingAppointmentInvoice"
                    class="indicator-label"
                  >
                    View Appointment Invoice
                  </span>

                  <span
                    v-if="viewingAppointmentInvoice"
                    class="indicator-progress"
                  >
                    Loading...
                    <span
                      class="spinner-border spinner-border-sm align-middle ms-2"
                    ></span>
                  </span>
                </button>
              </div>
            </div>

            <el-divider />

            <el-form @submit.prevent="handleSubmitPayment" ref="formRef">
              <div class="fv-row mb-4">
                <label class="text-muted fs-6 fw-bold mb-2 d-block">
                  Amount to Pay
                </label>
                <CurrencyInput
                  class="w-100"
                  v-model="formData.amount"
                  placeholder="Procedure Price"
                />
              </div>

              <div class="fv-row mb-4">
                <div
                  class="d-flex w-100 flex-row justify-content-between gap-4"
                >
                  <el-form-item class="mb-0" prop="payment_type">
                    <el-radio-group v-model="formData.payment_type">
                      <el-radio label="EFTPOS" size="large">Etfpos</el-radio>
                      <el-radio label="CASH" size="large">Cash</el-radio>
                    </el-radio-group>
                  </el-form-item>

                  <el-form-item class="mb-0" prop="is_send_receipt">
                    <el-checkbox
                      type="checkbox"
                      v-model="formData.is_send_receipt"
                      label="Send receipt?"
                    />
                  </el-form-item>
                </div>
              </div>

              <div class="fv-row">
                <button
                  type="submit"
                  class="btn btn-primary w-100"
                  :disabled="formData.amount === 0 || loading"
                  :data-kt-indicator="loading ? 'on' : null"
                >
                  <span v-if="!loading" class="indicator-label">Confirm</span>
                  <span v-if="loading" class="indicator-progress">
                    Submitting...
                    <span
                      class="spinner-border spinner-border-sm align-middle ms-2"
                    ></span>
                  </span>
                </button>
              </div>
            </el-form>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-5 mb-xxl-8">
      <div class="card-header pt-5">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold fs-3 mb-1">Previous Payments</span>
        </h3>
      </div>
      <div class="card-body pt-3 pb-0">
        <div class="row">
          <Datatable
            :table-header="prevPaymentsTableHeader"
            :table-data="billingData.payment_list"
            :enable-items-per-page-dropdown="false"
            empty-table-text="No items paid"
          >
            <template v-slot:cell-invoice_number="{ row: item }">
              {{ item.full_invoice_number }}
            </template>

            <template v-slot:cell-amount="{ row: item }">
              {{ convertToCurrency(item.amount / 100) }}
              <template v-if="item.amount < 0">
                <br />
                <span class="fs-7 fw-bold text-muted">
                  Authorized by: {{ item.authorizing_user_name }}
                </span>
              </template>
            </template>

            <template v-slot:cell-method="{ row: item }">
              {{ item.payment_type }}
            </template>

            <template v-slot:cell-date="{ row: item }">
              {{
                moment(item.created_at).format("DD/MM/YYYY HH:mm").toString()
              }}
            </template>

            <template v-slot:cell-confirmed_by="{ row: item }">
              {{ item.confirmed_user.full_name }}
            </template>

            <template v-slot:cell-actions="{ row: item }">
              <div class="">
                <button
                  class="btn btn-light-primary w-100 mt-6"
                  :disabled="sendingPaymentInvoice == item.id"
                  :data-kt-indicator="
                    sendingPaymentInvoice == item.id ? 'on' : null
                  "
                  @click="sendPaymentInvoice(item.id)"
                >
                  <span
                    v-if="sendingPaymentInvoice != item.id"
                    class="indicator-label"
                  >
                    Send Invoice to Patient
                  </span>

                  <span
                    v-if="sendingPaymentInvoice == item.id"
                    class="indicator-progress"
                  >
                    Sending...
                    <span
                      class="spinner-border spinner-border-sm align-middle ms-2"
                    ></span>
                  </span>
                </button>

                <button
                  class="btn btn-light-primary w-100 mt-2"
                  :disabled="viewingPaymentInvoice == item.id"
                  :data-kt-indicator="
                    viewingPaymentInvoice == item.id ? 'on' : null
                  "
                  @click="viewPaymentInvoice(item.id)"
                >
                  <span
                    v-if="viewingPaymentInvoice != item.id"
                    class="indicator-label"
                  >
                    View Invoice
                  </span>

                  <span
                    v-if="viewingPaymentInvoice == item.id"
                    class="indicator-progress"
                  >
                    Loading...
                    <span
                      class="spinner-border spinner-border-sm align-middle ms-2"
                    ></span>
                  </span>
                </button>
              </div>
            </template>
          </Datatable>
        </div>
      </div>
    </div>
    <!--end::Card-->

    <VerifyPinModal
      customMessage="You are attempting to issue a refund."
      v-on:verified="verifyRefund"
      v-on:closeModal="closePinConfirmModal"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "vuex";
import { setCurrentPageBreadcrumbs } from "@/core/helpers/breadcrumb";
import chargeTypes, {
  getProcedurePrice,
  getChargeTypes,
} from "@/core/data/charge-types";
import { convertToCurrency } from "@/core/data/billing";
import { Actions } from "@/store/enums/StoreEnums";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";
import Swal from "sweetalert2/dist/sweetalert2.js";
import InfoSection from "@/components/presets/GeneralElements/InfoSection.vue";
import CardSection from "../presets/GeneralElements/CardSection.vue";
import Datatable from "@/components/kt-datatable/KTDatatable.vue";
import IconButton from "@/components/presets/GeneralElements/IconButton.vue";
import PaymentItemModal from "@/components/make-payment/PaymentItemModal.vue";
import VerifyPinModal from "@/components/organisation-admins/VerifyPinModal.vue";
import { Modal } from "bootstrap";
import moment from "moment";

export default defineComponent({
  name: "make-payment-pay",
  components: {
    InfoSection,
    CardSection,
    Datatable,
    IconButton,
    PaymentItemModal,
    VerifyPinModal,
  },
  setup() {
    const store = useStore();
    const route = useRoute();
    const appointmentId = route.params.id;
    const billingData = computed(() => store.getters.paymentSelected);
    const total_amount = ref(0);
    const paymentItemModal = ref();
    const verifyPinModal = ref();
    const loading = ref(false);
    const sendingPaymentInvoice = ref<number | null>(null);
    const viewingPaymentInvoice = ref<number | null>(null);
    const sendingAppointmentInvoice = ref<boolean>(false);
    const viewingAppointmentInvoice = ref<boolean>(false);
    const authorizedBy = ref<number | null>(null);
    const refundVerified = ref(false);
    const formRef = ref<null | HTMLFormElement>(null);
    const formData = ref({
      appointment_id: 0,
      payment_type: "EFTPOS",
      amount: 0,
      is_deposit: false,
      is_send_receipt: true,
      sent_to: null,
    });
    const paymentItemModalData = ref({
      mode: "",
      category: "",
      item: null,
      key: 1,
    });

    const mbsTableHeader = ref([
      {
        name: "MBS Code",
        key: "mbs_code",
        sortable: true,
      },
      {
        name: "MBS Description",
        key: "description",
        sortable: false,
      },
      {
        name: "Price",
        key: "price",
        sortable: false,
      },
      {
        name: "Actions",
        key: "actions",
        sortable: false,
      },
    ]);

    const tableHeader = ref([
      {
        name: "Name",
        key: "name",
        sortable: true,
      },
      {
        name: "Description",
        key: "description",
        sortable: false,
      },
      {
        name: "Price",
        key: "price",
        sortable: false,
      },
      {
        name: "Actions",
        key: "actions",
        sortable: false,
      },
    ]);

    const prevPaymentsTableHeader = ref([
      {
        name: "Invoice Number",
        key: "invoice_number",
        sortable: true,
      },
      {
        name: "Amount",
        key: "amount",
        sortable: true,
      },
      {
        name: "Method",
        key: "method",
        sortable: true,
      },
      {
        name: "Date",
        key: "date",
        sortable: true,
      },
      {
        name: "Confirmed By",
        key: "confirmed_by",
        sortable: true,
      },
      {
        name: "Actions",
        key: "actions",
        sortable: false,
      },
    ]);

    const proceduresUndertakenList = computed(() => {
      let list = [] as Array<Record<string, unknown>>;

      if (Object.prototype.hasOwnProperty.call(billingData.value, "charges")) {
        const charges = billingData.value.charges.procedures;

        charges.forEach((charge) => {
          const item = {
            id: charge.id,
            price: charge.price,
            authorized_by: charge?.authorized_by
              ? charge.authorized_by?.id ?? charge.authorized_by
              : null,
            deleted_by: charge?.deleted_by
              ? charge.deleted_by?.id ?? charge.deleted_by
              : null,
          };

          list.push(item);
        });
      }

      return list;
    });

    const extraItemsList = computed(() => {
      let list = [] as Array<Record<string, unknown>>;

      if (Object.prototype.hasOwnProperty.call(billingData.value, "charges")) {
        const charges = billingData.value.charges.extra_items;

        charges.forEach((charge) => {
          const item = {
            id: charge.id,
            price: charge.price,
            authorized_by: charge?.authorized_by
              ? charge.authorized_by?.id ?? charge.authorized_by
              : null,
            deleted_by: charge?.deleted_by
              ? charge.deleted_by?.id ?? charge.deleted_by
              : null,
          };

          list.push(item);
        });
      }

      return list;
    });

    const adminItemsList = computed(() => {
      let list = [] as Array<Record<string, unknown>>;

      if (Object.prototype.hasOwnProperty.call(billingData.value, "charges")) {
        const charges = billingData.value.charges.admin_items;

        charges.forEach((charge) => {
          const item = {
            id: charge.id,
            price: charge.price,
            authorized_by: charge?.authorized_by
              ? charge.authorized_by?.id ?? charge.authorized_by
              : null,
            deleted_by: charge?.deleted_by
              ? charge.deleted_by?.id ?? charge.deleted_by
              : null,
          };

          list.push(item);
        });
      }

      return list;
    });

    const procedurePrice = computed(() => {
      let price = 0;
      if (Object.prototype.hasOwnProperty.call(billingData.value, "charges")) {
        price = getProcedurePrice(
          billingData.value.charges,
          billingData.value.appointment.charge_type
        );
      }

      return price;
    });

    const amountPaid = computed(() => {
      let total = 0;

      if (
        Object.prototype.hasOwnProperty.call(billingData.value, "payment_list")
      ) {
        billingData.value.payment_list.forEach((payment) => {
          total += payment.amount;
        });
      }

      return total;
    });

    const amountOutstanding = computed(() => {
      const total = procedurePrice.value - amountPaid.value;

      return total;
    });

    const handleDeleteItem = (authorizedBy) => {
      const category = paymentItemModalData.value.category;
      const item = paymentItemModalData.value.item;

      deleteItem(category, item, authorizedBy);
    };

    const handleEditItem = (category, item) => {
      if (!paymentItemModal.value) {
        paymentItemModal.value = new Modal(
          document.getElementById("modal_payment_item")
        );
      }

      paymentItemModalData.value.mode = "edit";
      paymentItemModalData.value.category = category;
      paymentItemModalData.value.item = item;
      paymentItemModalData.value.key++;

      paymentItemModal.value.show();
    };

    const handleAddItem = (category) => {
      if (!paymentItemModal.value) {
        paymentItemModal.value = new Modal(
          document.getElementById("modal_payment_item")
        );
      }

      paymentItemModalData.value.mode = "add";
      paymentItemModalData.value.category = category;
      paymentItemModalData.value.item = null;
      paymentItemModalData.value.key++;

      paymentItemModal.value.show();
    };

    const deleteItem = (category, item, authorizedBy) => {
      const deletingItem = billingData.value.charges[category].find(
        (charge) => charge.id === item.id
      );

      deletingItem.price = 0;
      deletingItem.deleted_by = authorizedBy;
      console.log(proceduresUndertakenList.value);
      updateAppointmentDetail();
    };

    const updatePrice = (item, price, category) => {
      const found = billingData.value.charges[category].find(
        (charge) => charge.id === item.id
      );

      found.price = price * 100;

      updateAppointmentDetail();
    };

    const addPaymentItem = (item) => {
      console.log("Pay", item);
      item.price = item.price * 100;
      billingData.value.charges[paymentItemModalData.value.category].push(item);
      updateAppointmentDetail();
    };

    const handlePaymentItemModalSubmit = (event) => {
      if (paymentItemModalData.value.mode === "add") {
        addPaymentItem(event);
      }

      if (paymentItemModalData.value.mode === "edit") {
        updatePrice(
          paymentItemModalData.value.item,
          event.price,
          paymentItemModalData.value.category
        );
      }

      handleCloseModal();
    };

    const handleCloseModal = () => {
      paymentItemModal.value.hide();
    };

    const closePinConfirmModal = () => {
      verifyPinModal.value.hide();
    };

    const verifyRefund = (authorizingUser) => {
      refundVerified.value = true;
      authorizedBy.value = authorizingUser;
      closePinConfirmModal();
    };

    const sendAppointmentInvoice = () => {
      sendingAppointmentInvoice.value = true;
      store.dispatch(Actions.INVOICE.SEND, appointmentId).finally(() => {
        sendingAppointmentInvoice.value = false;
      });
    };

    const sendPaymentInvoice = (id) => {
      sendingPaymentInvoice.value = id;
      store.dispatch(Actions.MAKE_PAYMENT.INVOICE.SEND, id).finally(() => {
        sendingPaymentInvoice.value = null;
      });
    };

    const viewAppointmentInvoice = () => {
      viewingAppointmentInvoice.value = true;
      store
        .dispatch(Actions.INVOICE.VIEW, appointmentId)
        .then((data) => {
          let blob = new Blob([data], { type: "application/pdf" });
          let objectUrl = URL.createObjectURL(blob);
          window.open(objectUrl, "_blank");
        })
        .finally(() => {
          viewingAppointmentInvoice.value = false;
        });
    };

    const viewPaymentInvoice = (id) => {
      viewingPaymentInvoice.value = id;
      store
        .dispatch(Actions.MAKE_PAYMENT.INVOICE.VIEW, id)
        .then((data) => {
          let blob = new Blob([data], { type: "application/pdf" });
          let objectUrl = URL.createObjectURL(blob);
          window.open(objectUrl, "_blank");
        })
        .finally(() => {
          viewingPaymentInvoice.value = null;
        });
    };

    const handleSubmitPayment = () => {
      if (formData.value.amount > amountOutstanding.value) {
        Swal.fire({
          text: "The payment amount is more than the amount owing. Are you sure you want to make this payment?",
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
            submit();
          }
        });

        return true;
      }

      if (formData.value.amount < 0 && refundVerified.value === false) {
        if (!verifyPinModal.value) {
          verifyPinModal.value = new Modal(
            document.getElementById("modal_verify_authorization_pin")
          );
        }

        verifyPinModal.value.show();
        return false;
      }

      submit();
    };

    const submit = () => {
      if (!formRef.value) {
        return;
      }
      formData.value.appointment_id = billingData.value.appointment.id;

      if (formData.value.is_send_receipt) {
        formData.value.sent_to =
          billingData.value.appointment.patient_details.email;
      }

      let submitData = {};

      if (authorizedBy.value) {
        submitData = {
          ...formData.value,
          authorized_by: authorizedBy.value,
        };
      } else {
        submitData = {
          ...formData.value,
        };
      }

      loading.value = true;

      store
        .dispatch(Actions.MAKE_PAYMENT.CREATE, submitData)
        .then(() => {
          store.dispatch(
            Actions.MAKE_PAYMENT.VIEW,
            formData.value.appointment_id
          );
          Swal.fire({
            text: "Payment Received.",
            icon: "success",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
              confirmButton: "btn btn-primary",
            },
          });
        })
        .catch(() => {
          Swal.fire({
            text: "Error submitting payment",
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
          refundVerified.value = false;
          authorizedBy.value = null;
        });
    };

    const updateAppointmentDetail = () => {
      const data = {
        appointment_id: billingData.value.appointment.id,
        procedures_undertaken: proceduresUndertakenList.value,
        extra_items_used: extraItemsList.value,
        admin_items: adminItemsList.value,
      };

      store.dispatch(AppointmentActions.DETAIL.UPDATE, data);
      store.dispatch(Actions.MAKE_PAYMENT.VIEW, formData.value.appointment_id);
    };

    onMounted(() => {
      setCurrentPageBreadcrumbs("Pay", ["Billing", "Out of Pocket"]);
      store.dispatch(Actions.MAKE_PAYMENT.VIEW, appointmentId).then(() => {
        formData.value.appointment_id = parseInt(appointmentId.toString());
      });
    });

    return {
      billingData,
      chargeTypes,
      formData,
      formRef,
      total_amount,
      submit,
      getProcedurePrice,
      getChargeTypes,
      amountPaid,
      procedurePrice,
      amountOutstanding,
      tableHeader,
      mbsTableHeader,
      handleDeleteItem,
      updatePrice,
      convertToCurrency,
      handleEditItem,
      paymentItemModalData,
      handlePaymentItemModalSubmit,
      handleCloseModal,
      handleAddItem,
      loading,
      handleSubmitPayment,
      verifyRefund,
      closePinConfirmModal,
      sendingPaymentInvoice,
      sendAppointmentInvoice,
      viewingPaymentInvoice,
      viewAppointmentInvoice,
      prevPaymentsTableHeader,
      moment,
      sendPaymentInvoice,
      viewPaymentInvoice,
      sendingAppointmentInvoice,
      viewingAppointmentInvoice,
    };
  },
});
</script>
