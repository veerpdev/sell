import IOrganization from "./IOrganization";

export default interface IUserAuth {
  organization: IOrganization;
  username: string;
  email: string;
  access_token: string;
  role: string;
  outside_hours: boolean;
  unreadDocuments: number;
}
