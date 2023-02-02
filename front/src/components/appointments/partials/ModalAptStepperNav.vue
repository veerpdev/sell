<template>
  <stepper-nav-item
    @click="$emit('goTo', 1)"
    dataStepperElement="nav"
    stepperNumber="1"
    stepperTitle="Appointment"
    stepperDescription="Setup Appointment Info"
  />

  <stepper-nav-item
    @click="$emit('goTo', 2)"
    dataStepperElement="nav"
    stepperNumber="2"
    stepperTitle="Patient Info"
    stepperDescription="Edit Patient Details"
  />

  <stepper-nav-item
    @click="$emit('goTo', 3)"
    dataStepperElement="nav"
    stepperNumber="3"
    stepperTitle="Patient Billing"
    stepperDescription="Edit Patient Billing Details"
  />

  <stepper-nav-item
    @click="$emit('goTo', 4)"
    dataStepperElement="nav"
    stepperNumber="4"
    stepperTitle="Other Info"
    stepperDescription="Referral and Appointment history"
  />
</template>

<script lang="ts">
import { defineComponent } from "vue";
import StepperNavItem from "@/components/presets/StepperElements/StepperNavItem.vue";
export default defineComponent({
  components: { StepperNavItem },
  emits: ["goTo"],
});
</script>
