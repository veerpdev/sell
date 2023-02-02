const PreAdmissionRoutes = [
  {
    path: "/appointment_pre_admissions/show",
    name: "pre-admission",
    component: () => import("@/views/pre-admission/index.vue"),
    children: [
      {
        path: "/appointment_pre_admissions/show/:id/form_1",
        name: "pre-admission-form1",
        component: () => import("@/components/pre-admission/Form1.vue"),
      },
      {
        path: "/appointment_pre_admissions/show/:id/form_2",
        name: "pre-admission-form2",
        component: () => import("@/components/pre-admission/Form2.vue"),
      },
      {
        path: "/appointment_pre_admissions/show/:id/form_3",
        name: "pre-admission-form3",
        component: () => import("@/components/pre-admission/Form3.vue"),
      },
    ],
  },
];

export default PreAdmissionRoutes;
