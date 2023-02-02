const ClinicRoutes = [
  {
    path: "/clinics",
    name: "clinics",
    component: () => import("@/views/settings/clinics/Clinics.vue"),
  },
  {
    path: "/clinics/create",
    name: "clinic-create",
    component: () => import("@/components/clinics/CreateClinics.vue"),
  },
  {
    path: "/clinics/edit/:id",
    name: "clinic-edit",
    component: () => import("@/components/clinics/CreateClinics.vue"),
  },
  {
    path: "/clinics/rooms/:id",
    name: "clinic-rooms",
    component: () => import("@/views/settings/clinics/Rooms.vue"),
  },
];

export default ClinicRoutes;
