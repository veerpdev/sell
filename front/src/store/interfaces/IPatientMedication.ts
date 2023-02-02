export default interface IPatientMedication {
  id: number;
  patient_id: number;
  name: string;
  instructions: string;
  start_date: string;
  end_date: string;
}
