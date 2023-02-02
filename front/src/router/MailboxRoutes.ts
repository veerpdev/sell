const MailboxRoutes = [
  {
    path: "/mailbox",
    name: "mailbox",
    component: () => import("@/views/mail/index.vue"),
    children: [
      {
        path: "list",
        name: "mailbox-list",
        component: () => import("@/views/mail/List.vue"),
      },
      {
        path: "compose",
        name: "mailbox-compose",
        component: () => import("@/views/mail/Compose.vue"),
      },
      {
        path: "view/:id",
        name: "mailbox-view",
        component: () => import("@/views/mail/View.vue"),
      },
      {
        path: "edit/:id",
        name: "mailbox-edit",
        component: () => import("@/views/mail/Edit.vue"),
      },
    ],
  },
];

export default MailboxRoutes;
