<template>
  <input class="currency-input el-input__inner" ref="inputRef" type="text" />
</template>

<script>
import { useCurrencyInput } from "vue-currency-input";

export default {
  name: "CurrencyInput",
  props: {
    modelValue: [Number, String], // Vue 2: value
    options: Object,
  },
  setup(props) {
    const { inputRef } = useCurrencyInput({ currency: "USD" });
    return { inputRef };
  },
};
</script>
