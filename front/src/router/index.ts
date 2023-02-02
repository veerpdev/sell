import { createRouter, createWebHashHistory, RouteRecordRaw } from "vue-router";
import store from "@/store";
import { Mutations, Actions } from "@/store/enums/StoreEnums";
import JwtService from "@/core/services/JwtService";
import AuthRoutes from "./AuthRoutes";
import AppointmentRoutes from "./AppointmentRoutes";
import BillingRoutes from "./BillingRoutes";
import MailboxRoutes from "./MailboxRoutes";
import PatientRoutes from "./PatientRoutes";
import SettingsRoutes from "./SettingsRoutes";
import HRMRoutes from "./HRMRoutes";
import DocumentRoutes from "./DocumentRoutes";
import ClinicRoutes from "./ClinicRoutes";
import OrganizationRoutes from "./OrganizationRoutes";
import PreAdmissionRoutes from "./PreAdmissionRoutes";
import MiscRoutes from "./MiscRoutes";
import { computed } from "vue";
import moment from "moment";

const routes: Array<RouteRecordRaw> = [
  {
    path: "/",
    redirect: "/sign-in",
    component: () => import("@/layout/Layout.vue"),
    children: [
      {
        path: "/admins",
        name: "administrator",
        component: () => import("@/views/Admins.vue"),
      },
      {
        path: "/coding",
        name: "coding-dashboard",
        component: () => import("@/views/coding/dashboard.vue"),
      },
      {
        path: "/coding-reports",
        name: "coding-reports",
        component: () => import("@/views/coding/report.vue"),
      },
      ...OrganizationRoutes,
      ...ClinicRoutes,
      ...DocumentRoutes,
      ...HRMRoutes,
      ...SettingsRoutes,
      ...PatientRoutes,
      ...MailboxRoutes,
      ...AppointmentRoutes,
      ...BillingRoutes,
      {
        path: "/profile",
        name: "profile",
        component: () => import("@/components/auth/Profile.vue"),
      },
      {
        path: "/profile/password-change",
        name: "password-change",
        component: () => import("@/components/auth/Password.vue"),
      },
      {
        path: "/profile/signature",
        name: "signature",
        component: () => import("@/components/auth/Signature.vue"),
      },
      {
        path: "/profile/auto-texts",
        name: "auto-texts",
        component: () => import("@/components/auth/AutoTextSettings.vue"),
      },
      {
        path: "/profile/auto-texts/create",
        name: "auto-text-create",
        component: () => import("@/components/auth/CreateAutoText.vue"),
      },
      {
        path: "/profile/auto-texts/edit/:id",
        name: "auto-text-edit",
        component: () => import("@/components/auth/CreateAutoText.vue"),
      },
      {
        path: "/employee-booking-dashboard",
        name: "employee-booking-dashboard",
        component: () => import("@/views/appointments/EmployeeBookings.vue"),
      },

      ////////////////////////////////////////////////////////////////////////
      // Anesthetist
      {
        path: "/anesthetist-dashboard",
        name: "anesthetist-dashboard",
        component: () => import("@/views/anesthetist/dashboard.vue"),
      },
    ],
  },
  ...AuthRoutes,
  ...PreAdmissionRoutes,
  ...MiscRoutes,
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

router.beforeEach(async (to) => {
  // reset config to initial state
  store.commit(Mutations.RESET_LAYOUT_CONFIG);

  await verifyToken();

  const isUserAuthenticated = computed(() => store.getters.isUserAuthenticated);
  const user = computed(() => store.getters.currentUser);
  const organization = computed(() => store.getters.userOrganization);
  const allowedRoutes = ["my-schedule", "sign-in"];

  // Scroll page to top on every route change
  setTimeout(() => {
    window.scrollTo(0, 0);
  }, 100);

  if (isUserAuthenticated.value && !user.value.outside_hours) {
    if (
      allowedRoutes.includes(<string>to.name) ||
      user.value.role == "organizationAdmin"
    ) {
      return true;
    }

    const orgStartTime = moment(organization.value.start_time, "HH:mm:ss");
    const orgEndTime = moment(organization.value.end_time, "HH:mm:ss");

    if (moment().isAfter(orgStartTime) && moment().isBefore(orgEndTime))
      return true;
    return false;
  }
});

const verifyToken = () => {
  return new Promise(function (resolve) {
    store
      .dispatch(Actions.VERIFY_AUTH, {
        api_token: JwtService.getToken(),
      })
      .then(() => {
        resolve("");
      });
  });
};

export default router;
