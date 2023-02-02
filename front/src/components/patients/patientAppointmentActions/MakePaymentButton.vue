<template>
  <LargeIconButton text="Make Payment" @click="handlePay" />
</template>

<script lang="ts">
import { Actions } from "@/store/enums/StoreEnums";
import store from "@/store";
import { useRouter } from "vue-router";
export default {
  components: {},
  props: {
    appointment: {
      required: true,
      type: Object,
    },
    patient: {
      required: true,
      type: Object,
    },
  },
  setup(props) {
    const router = useRouter();
    const handlePay = () => {
      store
        .dispatch(Actions.MAKE_PAYMENT.VIEW, props.appointment.id)
        .then(() => {
          setTimeout(() => {
            router.push({ name: "make-payment-pay" });
          }, 100);
        });
    };
    return { handlePay };
  },
};
</script>
