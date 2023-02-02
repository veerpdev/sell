export default interface IPatientBilling {
  id: string;
  patient_id: string;
  member_number: string;
  member_reference_number: string;
  health_fund_id: string;
  has_medicare_concession: string;
  billing_type: string;
  verified_at: string;
  is_valid: string;
}
