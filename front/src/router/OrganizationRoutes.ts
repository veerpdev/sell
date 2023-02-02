const OrganizationRoutes = [
  {
    path: "/organisations",
    name: "organisations",
    component: () => import("@/views/Organisations.vue"),
  },
  {
    path: "/organisations/create",
    name: "createOrganisation",
    component: () => import("@/components/organisations/AddOrganisation.vue"),
  },
  {
    path: "/organisations/edit/:id",
    name: "editOrganisation",
    component: () => import("@/components/organisations/EditOrganisation.vue"),
  },
  {
    path: "settings/organization",
    name: "organization",
    component: () => import("@/views/organization/Organization.vue"),
  },
];

export default OrganizationRoutes;
