export default interface IPatientAlert {
  id: string;
  patient_id: string;
  created_by: string;
  created_by_name: string;
  alert_level: string;
  title: string;
  explanation: string;
  is_dismissed: string;
  created_at: string;
}
