import OrgManagerMenu from "@/core/config/menus/OrgManagerMenu";
import AdminMenu from "@/core/config/menus/AdminMenu";
import ReceptionistMenu from "@/core/config/menus/ReceptionistMenu";
import SpecialistMenu from "@/core/config/menus/SpecialistMenu";
import PathologistMenu from "@/core/config/menus/PathologistMenu";
import ScientistMenu from "@/core/config/menus/ScientistMenu";
import TypistMenu from "@/core/config/menus/TypistMenu";
import AnesthetistMenu from "@/core/config/menus/AnesthetistMenu";

const MainMenuConfig = {
  admin: AdminMenu,
  organizationManager: OrgManagerMenu,
  receptionist: ReceptionistMenu,
  specialist: SpecialistMenu,
  pathologist: PathologistMenu,
  scientist: ScientistMenu,
  typist: TypistMenu,
  anesthetist: AnesthetistMenu,
};

export default MainMenuConfig;
