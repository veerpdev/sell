const SettingsRoutes = [
  {
    path: "/settings/pre-admission-questions",
    name: "pre-admission-questions",
    component: () => import("@/views/settings/PreAdmissionQuestions.vue"),
  },
  {
    path: "/settings/pre-admission-consent",
    name: "pre-admission-consent",
    component: () => import("@/views/settings/PreAdmissionConsent.vue"),
  },
  {
    path: "/settings/notification-templates",
    name: "notificationTemplates",
    component: () =>
      import(
        "@/views/settings/notification-templates/NotificationTemplates.vue"
      ),
  },
  {
    path: "/settings/document-templates",
    name: "documentTemplates",
    component: () =>
      import("@/views/settings/document-templates/DocumentTemplates.vue"),
  },
  {
    path: "/settings/patient-print-label-setting",
    name: "patientPrintLabelSettings",
    component: () => import("@/views/settings/PatientLabelPrintSettings.vue"),
  },
  {
    path: "/settings/health-fund",
    name: "healthFund",
    component: () => import("@/views/settings/health-fund/HealthFund.vue"),
  },
  {
    path: "/settings",
    name: "manager-settings",
    component: () => import("@/views/settings/ManagerSettings.vue"),
  },
  {
    path: "/settings/apt-types",
    name: "aptTypes",
    component: () => import("@/views/settings/apt-types/AptTypeList.vue"),
  },
  {
    path: "/settings/apt-types/create",
    name: "createAptType",
    component: () => import("@/views/settings/apt-types/EditAptType.vue"),
  },
  {
    path: "/settings/apt-types/edit/:id",
    name: "editAptType",
    component: () => import("@/views/settings/apt-types/EditAptType.vue"),
  },
  {
    path: "/settings/time-requirements",
    name: "timeRequirements",
    component: () =>
      import("@/views/settings/time-requirements/AptTimeRequirement.vue"),
  },
  {
    path: "/settings/anesthetic-questions",
    name: "anesthetic-questions",
    component: () =>
      import("@/views/settings/anesthetic-questions/AnestheticQuestions.vue"),
  },
  {
    path: "/settings/doctor-address-books",
    name: "refDoctors",
    component: () =>
      import("@/views/settings/doctor-address-books/RefDoctors.vue"),
  },
  {
    path: "/settings/doctor-address-books/create",
    name: "createRefDoctors",
    component: () =>
      import("@/views/settings/doctor-address-books/EditRefDoctors.vue"),
  },
  {
    path: "/settings/doctor-address-books/edit/:id",
    name: "editRefDoctors",
    component: () =>
      import("@/views/settings/doctor-address-books/EditRefDoctors.vue"),
  },
  {
    path: "/settings/header-footer-templates",
    name: "headerFooterTemplates",
    component: () =>
      import(
        "@/views/settings/header-footer-templates/HeaderFooterTemplates.vue"
      ),
  },
  {
    path: "/setting/billing-items",
    name: "setting-billing-items",
    component: () =>
      import("@/views/organisation-admin/billing-setting/ScheduleItemView.vue"),
  },
];

export default SettingsRoutes;
