<template>
  <div
    class="p-4 mb-4 card border border-dashed border-primary d-flex flex-column gap-2"
  >
    <info-section heading="Appointment Type">
      {{ appointmentName }}
    </info-section>

    <info-section heading="Specialist">
      {{ specialist?.full_name }}
    </info-section>
    <info-section heading="Anesthetist" v-if="anesthetistName"
      >{{ anesthetistName }}
    </info-section>

    <InfoSection heading="Clinic">{{ aptInfoData.clinic_name }}</InfoSection>

    <info-section heading="Time">
      {{ aptInfoData.time_slot[0] }}
      - {{ aptInfoData.time_slot[1] }}
      <span v-if="aptInfoData.arrival_time" class="text-black fs-5">
        (Arrive: {{ aptInfoData.arrival_time }})</span
      ></info-section
    >
    <info-section heading="Date">
      {{ aptInfoData.date }}
    </info-section>

    <info-section heading="Patient"
      >{{ patientInfoData.first_name }}
      {{ patientInfoData.last_name }}
    </info-section>
  </div>
</template>

<script lang="ts">
import InfoSection from "@/components/presets/GeneralElements/InfoSection.vue";
import { computed, PropType, ref, watch } from "vue";
import { ICurSpecialist } from "@/assets/ts/components/_CreateAppointmentComponent";
import { useStore } from "vuex";

export default {
  components: {
    InfoSection,
  },

  props: {
    specialist: {
      required: true,
      type: Object as PropType<ICurSpecialist>,
    },
    aptInfoData: {
      required: true,
      type: Object,
    },
    patientInfoData: {
      required: true,
      type: Object,
    },
  },

  setup(props) {
    const store = useStore();
    const anesthetistName = ref<string>();

    const getAnesthetistName = () => {
      if (props.specialist.hrm_work_schedule) {
        props.specialist.hrm_work_schedule.map((slot) => {
          if (
            slot.anesthetist_id &&
            slot.clinic_id === props.aptInfoData.clinic_id &&
            props.aptInfoData.time_slot[0] >= slot.start_time &&
            props.aptInfoData.time_slot[1] < slot.end_time
          ) {
            anesthetistName.value = slot.anesthetist.full_name;
          }
        });
      }
    };

    watch(
      () => props.appointmentName,
      () => {
        getAnesthetistName();
      }
    );
    watch(
      () => props.specialist,
      () => {
        getAnesthetistName();
      }
    );

    const appointmentName = computed(() => {
      const id = props.aptInfoData.appointment_type_id;
      if (id) {
        const aptTypeList = store.getters.getAptTypesList;
        const selectedType = aptTypeList.filter(
          (aptType) => aptType.id === id
        )[0];
        if (selectedType.name) return selectedType.name;
      }
      return "";
    });

    return {
      getAnesthetistName,
      anesthetistName,
      appointmentName,
    };
  },
};
</script>
