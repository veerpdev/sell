<template>
  <el-input
    type="number"
    min="1"
    v-model="label_count"
    placeholder="Enter label print count"
  />
  <LargeIconButton text="Print Label" @click="handlePrint()" />
</template>

<script lang="ts">
import IPatient from "@/store/interfaces/IPatient";
import { PropType, ref } from "vue";
import { printPatientLabel } from "@/helpers/helpers";
import IAppointment from "@/store/interfaces/IAppointment";
import { StorageKey } from "@/core/enum/storage-key";
import { setLocalStorage, getLocalStorage } from "@/utils/LocalStorage.Util";

export default {
  props: {
    appointment: {
      required: true,
      type: Object as PropType<IAppointment>,
    },
    patient: {
      required: true,
      type: Object as PropType<IPatient>,
    },
  },
  setup(props) {
    const default_count = getLocalStorage(StorageKey.PrintLabelCount)
      ? Number(getLocalStorage(StorageKey.PrintLabelCount))
      : 1;
    const label_count = ref(default_count);
    const handlePrint = () => {
      const printCount = label_count.value;
      setLocalStorage(StorageKey.PrintLabelCount, printCount);
      for (var i = 0; i < printCount; i++) {
        handlePrintLabel();
      }
    };
    const handlePrintLabel = () => {
      printPatientLabel(props.patient, props.appointment, "ZDesigner GK420d");
    };

    return {
      handlePrint,
      label_count,
    };
  },
};
</script>
