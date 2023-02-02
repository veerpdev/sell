const BillingRoutes = [
  {
    path: "/billing/make-payment",
    name: "make-payment",
    component: () => import("@/views/MakePayment.vue"),
  },
  {
    path: "/billing/make-payment/pay/:id",
    name: "make-payment-pay",
    component: () => import("@/components/make-payment/Pay.vue"),
  },
  {
    path: "/billing/make-payment/view",
    name: "make-payment-view",
    component: () => import("@/components/make-payment/View.vue"),
  },
];

export default BillingRoutes;
