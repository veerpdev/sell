export default interface IOutgoingMessageLog {
  id: number;
  organization_id: number;
  patient_id: number;
  document_id: number;
  message_contents: string;
  sending_user: number;
  sending_doctor_user: number;
  send_method: string;
  send_status: string;
  sending_doctor_name: string;
  sending_doctor_provider: string;
  receiving_doctor_name: string;
  receiving_doctor_provider: string;
  created_at: string;
  sending_user_name: string;
  patient: {
    title: string;
    first_name: string;
    last_name: string;
    date_of_birth: string;
  };
}
