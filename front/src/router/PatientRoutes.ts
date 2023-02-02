const PatientRoutes = [
  {
    path: "/patients",
    name: "patients",
    component: () => import("@/views/patients/PatientList.vue"),
  },
  {
    path: "/patients/:id",
    name: "patients-view",
    component: () => import("@/views/patients/PatientView.vue"),
    children: [
      {
        path: "appointments",
        name: "patients-view-appointments",
        component: () => import("@/views/patients/Appointments.vue"),
      },
      {
        path: "claim-sources",
        name: "patients-view-claim-sources",
        component: () => import("@/views/patients/ClaimSources.vue"),
      },
      {
        path: "recalls",
        name: "patients-recalls",
        component: () => import("@/views/patients/Recalls.vue"),
      },
      {
        path: "administration",
        name: "patients-view-administration",
        component: () => import("@/views/patients/Administration.vue"),
      },
      {
        path: "documents",
        name: "patients-view-documents",
        component: () => import("@/views/patients/Documents.vue"),
      },
      {
        path: "clinical",
        name: "patients-view-clinical",
        component: () => import("@/views/patients/Clinical.vue"),
      },
    ],
  },
  {
    path: "/patients/:patientId/document/:appointmentId/:documentType",
    name: "patients-document",
    component: () => import("@/views/patients/CreateDocument.vue"),
  },
  {
    path: "/patients/add",
    name: "add-patient",
    component: () => import("@/views/patients/AddPatient.vue"),
  },
];

export default PatientRoutes;
