<template>
  <template v-if="timeSlots.length > 0">
    <div
      v-for="(slot, index) in timeSlots"
      :key="slot.id"
      class="my-4 p-2 border border-secondary"
    >
      <span class="apt-description caption-content">
        {{ specialist.first_name + "'s" }}
        Schedule Timeslot -
        {{ index + 1 }}
      </span>
      <br />
      <span class="caption-content">Time: </span>
      <span>{{ slot.start_time }} - {{ slot.end_time }}</span>
      <br />
      <span class="caption-content"> Clinic Name: </span>
      <span>{{ slot.clinic_name }}</span>
      <br />
      <span class="caption-content"> Restriction: </span>
      <span>{{ slot.restriction }}</span>
    </div>
  </template>
  <template v-else>
    <span class="select-new-apt-caption">
      Selected Specialist doesn't work on selected date</span
    >
  </template>
</template>

<script lang="ts">
import { computed, defineComponent } from "vue";
import { useStore } from "vuex";
import { ISpecialist } from "@/store/modules/SpecialistsModule";

export default defineComponent({
  name: "bulk-move-apt-modal",
  props: {
    timeSlots: { type: Object, required: true },
    specialistId: { type: Number, required: false },
  },
  setup(props) {
    const store = useStore();

    const specialist = computed(() => {
      return store.getters.getSpecialistList.filter(
        (spt: ISpecialist) => spt.id === props.specialistId
      )[0];
    });

    return {
      props,
      specialist,
    };
  },
});
</script>

<style scoped>
.appointment-type label {
  display: none;
}

.select-new-apt-caption {
  color: red;
  text-transform: uppercase;
  font-weight: bold;
}

.apt-description {
  font-size: 16px;
  padding-bottom: 15px;
}

.caption-content {
  color: #3e7ba0;
  font-weight: 700;
}
</style>
