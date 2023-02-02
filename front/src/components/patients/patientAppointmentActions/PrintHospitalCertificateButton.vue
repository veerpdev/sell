<template>
  <LargeIconButton
    text="Print Hospital Certificate"
    @click="handlePrintHospitalCertificate()"
  />
</template>

<script lang="ts">
export default {
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
  setup() {
    const handlePrintHospitalCertificate = () => {
      console.log("Placeholder: Print Hospital Certificate");
    };

    return { handlePrintHospitalCertificate };
  },
};
</script>
