import LayoutConfigTypes from "@/core/config/LayoutConfigTypes";

const config: LayoutConfigTypes = {
  themeName: "Metronic",
  themeVersion: "8.0.38",
  demo: "demo6",
  main: {
    type: "default",
    primaryColor: "#3D7EA1",
    logo: {
      dark: "aurora-sml-logo.svg",
      light: "aurora-sml-logo.svg",
    },
  },
  illustrations: {
    set: "sketchy-1",
  },
  loader: {
    logo: "aurora-sml-logo.svg",
    display: true,
    type: "default",
  },
  scrollTop: {
    display: true,
  },
  header: {
    display: true,
    menuIcon: "svg",
    width: "fluid",
    fixed: {
      desktop: true,
      tabletAndMobile: true,
    },
  },
  toolbar: {
    display: true,
    width: "fluid",
    fixed: {
      desktop: false,
      tabletAndMobile: false,
    },
  },
  aside: {
    display: true,
    theme: "dark",
    fixed: true,
    menuIcon: "svg",
    minimized: false,
    minimize: true,
    hoverable: true,
  },
  content: {
    width: "fixed",
  },
  footer: {
    width: "fluid",
  },
};

export default config;
