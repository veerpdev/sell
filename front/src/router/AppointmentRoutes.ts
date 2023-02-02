const AppointmentRoutes = [
  {
    path: "/appointments/dashboard",
    name: "booking-dashboard",
    component: () => import("@/views/appointments/Appointments.vue"),
  },
  {
    path: "/appointments/unconfirmed-apts",
    name: "unconfirmed-apts",
    component: () => import("@/views/appointments/UnconfirmedApts.vue"),
  },
  {
    path: "/appointments/unapproved-procedures",
    name: "unapproved-procedures",
    component: () => import("@/views/appointments/UnapprovedProcedure.vue"),
  },
  {
    path: "/appointments/waitlisted-apts",
    name: "waitlisted-apts",
    component: () => import("@/views/appointments/WaitlistedApts.vue"),
  },
  {
    path: "/appointments/cancellation-list",
    name: "cancellation-list",
    component: () => import("@/views/appointments/CancellationList.vue"),
  },
  {
    path: "/appointment/print-hospital-certificate",
    name: "appointment-print-hospital-certificate-view",
    component: () =>
      import("@/views/appointments/PrintHospitalCertificate.vue"),
  },
  {
    path: "/appointments/deallocate-appointments",
    name: "deallocate-appointments",
    component: () => import("@/views/appointments/DeallocateAppointments.vue"),
  },
];

export default AppointmentRoutes;
