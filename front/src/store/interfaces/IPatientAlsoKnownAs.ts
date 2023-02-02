export default interface IPatientAlsoKnownAs {
  id: number | null;
  patient_id: number;
  first_name: string;
  last_name: string;
  is_delete: boolean;
}
