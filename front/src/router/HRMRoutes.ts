const HRMRoutes = [
  {
    path: "/employees",
    name: "employees",
    component: () => import("@/views/employees/Employee.vue"),
  },
  {
    path: "/employees/create",
    name: "employees-create",
    component: () => import("@/views/employees/EditEmployee.vue"),
  },
  {
    path: "/employees/edit/:id",
    name: "employees-edit",
    component: () => import("@/views/employees/EditEmployee.vue"),
  },
  {
    path: "/hrm/weekly-schedule",
    name: "hrm-weekly-schedule",
    component: () => import("@/views/HRM/WeeklySchedule.vue"),
  },
  {
    path: "/hrm/weekly-schedule-template",
    name: "hrm-weekly-schedule-template",
    component: () => import("@/views/HRM/WeeklyScheduleTemplate.vue"),
  },
  {
    path: "/hrm/bulletin-board",
    name: "bulletin-board",
    component: () => import("@/views/HRM/BulletinBoard.vue"),
  },
  {
    path: "/hrm/bulletin-manage",
    name: "bulletin-manage",
    component: () => import("@/views/HRM/BulletinManage.vue"),
  },
  {
    path: "/hrm/my-schedule",
    name: "my-schedule",
    component: () => import("@/views/HRM/MySchedule.vue"),
  },
  {
    path: "/hrm/my-availability",
    name: "my-availability",
    component: () => import("@/views/HRM/MyAvailability.vue"),
  },
  {
    path: "/hrm/employee-availabilities",
    name: "Employee Availabilities",
    component: () => import("@/views/HRM/EmployeeLeave.vue"),
  },
];

export default HRMRoutes;
