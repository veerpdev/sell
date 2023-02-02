<template>
  <!--begin::Card-->
  <div class="card">
    <!--begin::Card body-->
    <div class="card-body">
      <!--begin::Calendar-->
      <template v-if="calendarOptions">
        <FullCalendar
          ref="appointmentCalendarRef"
          :key="calendarKey"
          :options="calendarOptions"
        >
          <template v-slot:eventContent="event">
            <AppointmentTableData
              :appointment="event.event.extendedProps.appointment"
            />
          </template>
        </FullCalendar>
      </template>
      <!--end::Calendar-->
    </div>
    <!--end::Card body-->
  </div>
  <!--end::Card-->
</template>

<script>
import { defineComponent, onMounted, computed, ref, watch } from "vue";
import { useStore } from "vuex";
import {
  AppointmentActions,
  AppointmentMutations,
} from "@/store/enums/StoreAppointmentEnums";
import { DrawerComponent } from "@/assets/ts/components/_DrawerComponent";

import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";
import interactionPlugin from "@fullcalendar/interaction";

import AppointmentTableData from "@/components/appointments/partials/AppointmentTableData.vue";
export default defineComponent({
  name: "employee-bookings-dashboard",
  components: {
    FullCalendar,
    AppointmentTableData,
  },
  setup() {
    const store = useStore();

    const appointmentCalendarRef = ref(null);
    const calendarKey = ref(0);

    const appointmentsRaw = computed(() => store.getters.getAptList);
    const appointments = ref([]);
    const userProfile = computed(() => store.getters.userProfile);
    const organization = computed(() => store.getters.userOrganization);

    const handleEventClick = (info) => {
      info.jsEvent.preventDefault();
      let appointment = info.event.extendedProps.appointment;
      store.commit(AppointmentMutations.SET_APT.SELECT, appointment);
      DrawerComponent?.getInstance("appointment-drawer")?.toggle();
    };

    const calendarOptions = ref(null);

    watch(userProfile, () => {
      let specialist_id =
        userProfile.value.role.id == 5 ? userProfile.value.id : null;
      let anesthetist_id =
        userProfile.value.role.id == 9 ? userProfile.value.id : null;

      store.dispatch(AppointmentActions.LIST, {
        specialist_id: specialist_id,
        anesthetist_id: anesthetist_id,
      });
    });

    watch(appointments, () => {
      if (appointmentCalendarRef.value) {
        let calenderAPI = appointmentCalendarRef.value.getApi();
        calenderAPI.removeAllEvents();
        for (let index = 0; index < appointments.value.length; index++) {
          appointmentCalendarRef.value
            .getApi()
            .addEvent(appointments.value[index]);
        }
      }
    });

    watch(appointmentsRaw, () => {
      appointments.value = [];
      appointmentsRaw.value.forEach((appointment) => {
        appointments.value.push({
          id: appointment.id,
          resourceId: appointment.specialist_id,
          start: appointment.date + "T" + appointment.start_time,
          end: appointment.date + "T" + appointment.end_time,
          appointment: appointment,
        });
      });
    });

    onMounted(() => {
      calendarOptions.value = {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        headerToolbar: {
          left: "prev,next today",
          center: "title",
          right: "timeGridWeek,timeGridDay",
        },
        initialView: "timeGridDay",
        navLinks: true, // can click day/week names to navigate views
        selectable: true,
        selectMirror: false,
        allDaySlot: false,
        slotMinTime: organization.value.start_time,
        slotMaxTime: organization.value.end_time,
        views: {
          timeGridWeek: { buttonText: "week" },
          timeGridDay: { buttonText: "day" },
        },

        editable: false,
        dayMaxEvents: false,
        events: [],
        eventClick: handleEventClick,
      };
      window.setInterval(() => {
        if (userProfile.value) {
          let specialist_id =
            userProfile.value.role.id == 5 ? userProfile.value.id : null;
          let anesthetist_id =
            userProfile.value.role.id == 9 ? userProfile.value.id : null;

          store.dispatch(AppointmentActions.LIST, {
            specialist_id: specialist_id,
            anesthetist_id: anesthetist_id,
          });
        }
      }, 8000);
    });

    return {
      calendarOptions,
      handleEventClick,
      appointmentCalendarRef,
      calendarKey,
    };
  },
});
</script>

<style lang="scss">
.fc-timegrid-slot {
  height: 70px !important;
}

.fc-timegrid-event {
  cursor: pointer;
}
</style>
