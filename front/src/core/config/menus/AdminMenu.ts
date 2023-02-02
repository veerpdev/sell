import icons from "@/core/data/icons";
const AdminMenu = [
  {
    heading: "Organisations",
    route: "/organisations",
    svgIcon: "media/icons/duotune/general/gen019.svg",
  },
  {
    heading: "Administrators",
    route: "/admins",
    svgIcon: icons.person_circle,
    fontIcon: "bi-person",
  },
  {
    heading: "Settings",
    route: "/settings",
    svgIcon: icons.gear,

    pages: [
      {
        heading: "Health Fund List",
        route: "/settings/health-fund",
      },
    ],
  },
];

export default AdminMenu;
