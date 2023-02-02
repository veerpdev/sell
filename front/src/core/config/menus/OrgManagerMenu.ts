import icons from "@/core/data/icons";
const OrgManagerMenu = [
  {
    heading: "Booking",
    route: "/appointments",
    svgIcon: icons.calender,
    pages: [
      {
        heading: "Dashboard",
        route: "/appointments/dashboard",
      },
      {
        heading: "Unconfirmed Appointments",
        route: "/appointments/unconfirmed-apts",
      },
      {
        heading: "Unapproved Procedures",
        route: "/appointments/unapproved-procedures",
      },
      {
        heading: "Waitlisted Appointments",
        route: "/appointments/waitlisted-apts",
      },

      {
        heading: "Deallocate Appointments",
        route: "/appointments/deallocate-appointments",
      },
      {
        heading: "Cancellation List",
        route: "/appointments/cancellation-list",
      },
    ],
  },
  {
    heading: "Patients",
    route: "/patients",
    svgIcon: icons.heart_pulse,
  },
  {
    heading: "Billing",
    route: "/billing",
    svgIcon: icons.credit_card,
    pages: [
      {
        heading: "Out of pocket payment",
        route: "/billing/make-payment",
      },
      {
        heading: "3rd Party Billing",
        route: "/billing",
      },
    ],
  },
  {
    heading: "Coding",
    route: "/coding",
    svgIcon: icons.paper,
    pages: [
      {
        heading: "Coding List",
        route: "/coding",
      },
      {
        heading: "Coding Reports",
        route: "/coding-reports",
      },
    ],
  },
  {
    heading: "Communication",
    route: "/third-party-comms",
    svgIcon: icons.paper_plane,
    fontIcon: "bi-send",
    pages: [
      {
        heading: "Document Inbox",
        route: "/inbox",
      },
      {
        heading: "Patient Recall",
        route: "/recalls",
      },
      {
        heading: "Outgoing Message Log",
        route: "/outgoing-log",
      },
    ],
  },
  {
    heading: "HRM",
    route: "/human-resource-management",
    svgIcon: icons.person_circle,
    pages: [
      {
        heading: "Weekly Schedule Template",
        route: "/hrm/weekly-schedule-template",
      },
      {
        heading: "Weekly Schedule",
        route: "/hrm/weekly-schedule",
      },
      {
        heading: "All Employees",
        route: "/employees",
      },
      {
        heading: "Administrators",
        route: "/organisationAdmins",
      },
      {
        heading: "Bulletin Board",
        route: "/hrm/bulletin-board",
      },
      {
        heading: "Employee Availabilities",
        route: "/hrm/employee-availabilities",
      },
      {
        heading: "Manage Bulletins",
        route: "/hrm/bulletin-manage",
      },
    ],
  },
];

export default OrgManagerMenu;
