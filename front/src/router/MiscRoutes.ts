const MiscRoutes = [
  {
    // the 404 route, when none of the above matches
    path: "/404",
    name: "404",
    component: () => import("@/views/Error404.vue"),
  },
  {
    path: "/500",
    name: "500",
    component: () => import("@/views/Error500.vue"),
  },
  {
    path: "/:pathMatch(.*)*",
    redirect: "/404",
  },
];

export default MiscRoutes;
