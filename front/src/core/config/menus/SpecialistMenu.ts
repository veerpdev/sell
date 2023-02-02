import icons from "@/core/data/icons";
import store from "@/store";
const SpecialistMenu = [
  {
    heading: "Bookings",
    route: "/employee-booking-dashboard",
    svgIcon: icons.calender,
  },
  {
    heading: "HRM",
    role: ["admin", "org_manager"],
    route: "/human-resource-management",
    svgIcon: icons.person_circle,
    pages: [
      {
        heading: "Bulletin Board",
        route: "/hrm/bulletin-board",
      },
      {
        heading: "My Schedule",
        route: "/hrm/my-schedule",
      },
      {
        heading: "My availability",
        route: "/hrm/my-availability",
      },
    ],
  },
  {
    heading: "Patients",
    route: "/patients",
    svgIcon: icons.heart_pulse,
  },
  {
    heading: "Incoming Communication",
    route: "/specialist/incoming-documents",
    svgIcon: icons.paper_plane,
    badge: true,
    badgeCount: "IncomingDocuments",
  },
  {
    heading: "Outgoing Communication",
    route: "/outgoing-log",
    svgIcon: icons.paper_plane,
    reverse: true,
  },
];

export default SpecialistMenu;
