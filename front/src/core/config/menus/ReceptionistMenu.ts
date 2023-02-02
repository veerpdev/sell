// import icons from "@/core/data/icons";
import icons from "@/core/data/icons";
const ReceptionistMenu = [
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
        heading: "Deallocate Appointments",
        route: "/appointments/deallocate-appointments",
      },
    ],
  },
];

export default ReceptionistMenu;
