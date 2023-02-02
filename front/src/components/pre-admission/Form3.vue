<template>
  <div class="card w-100 h-100 p-12">
    <div class="card-header border-0 p-5">
      <img
        :src="orgData.organization_logo"
        alt="Organization Logo"
        class="mb-10 px-6 mx-auto d-block text-center"
      />
    </div>
    <div class="card-body pt-0">
      <div class="w-50 fs-4 m-auto text-center">
        <p>Thank you for providing your details.</p>

        <p>
          If you have any further questions, please contact
          {{ orgData.clinic }} on {{ orgData.clinic_phone }} or email us at
          {{ orgData.clinic_email }}
        </p>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref, computed } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { AppointmentActions } from "@/store/enums/StoreAppointmentEnums";

export default defineComponent({
  name: "pre-admission-form3",

  components: {},

  setup() {
    const router = useRouter();
    const store = useStore();
    const orgData = computed(() => store.getters.getAptPreAdmissionOrg);
    const apt_id = ref("");

    onMounted(() => {
      apt_id.value = router.currentRoute.value.params.id.toString();
      store.dispatch(
        AppointmentActions.PRE_ADMISSION.ORGANIZATION,
        apt_id.value
      );
    });

    return {
      orgData,
    };
  },
});
</script>
