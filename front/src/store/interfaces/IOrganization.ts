export default interface IOrganization {
  id: number;
  abn_acn: string;
  appointment_length: number;
  clinic_count: number;
  code: string;
  created_at: string;
  end_time: string;
  formatted_abn_acn: string;
  has_billing: boolean;
  has_coding: boolean;
  ip_whitelist: string;
  is_hospital: boolean;
  is_max_clinics: boolean;
  is_max_users: boolean;
  logo: string;
  logo_url: string;
  max_clinics: number;
  max_employees: number;
  name: string;
  owner_id: number;
  password_expiration_timeframe: number;
  start_time: string;
  status: string;
  updated_at: string;
  user_count: number;
}
