export default interface IBulletin {
  body: string;
  created_at: string;
  created_by: number;
  created_by_name: string;
  expiry_date: string;
  id: number;
  included_roles: string;
  organization_id: number;
  title: string;
  updated_at: string;
}
