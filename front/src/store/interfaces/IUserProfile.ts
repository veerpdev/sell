import IUserRole from "./IUserRole";

export default interface IUserProfile {
  id: number;
  organization_id: number;
  full_name: string;
  first_name: string;
  last_name: string;
  email: string;
  mobile_number: string;
  address: string;
  photo: string;
  username: string;
  role: IUserRole;
}
