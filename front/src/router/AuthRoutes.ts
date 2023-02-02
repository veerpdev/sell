const AuthRoutes = [
  {
    path: "/",
    component: () => import("@/components/auth/Auth.vue"),
    children: [
      {
        path: "/sign-in",
        name: "sign-in",
        component: () => import("@/views/SignIn.vue"),
      },
    ],
  },
];

export default AuthRoutes;
