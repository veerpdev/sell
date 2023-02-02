export default interface IAppointmentType {
  id: number;
  name: string;
  organization_id: number;
  appointment_id: number;
  anesthetist_required: boolean;
  collecting_person_required: boolean;
  appointment_length_as_number: number;
  appointment_time: string;
  arrival_time: number;
  color: string;
  invoice_by: string;
  default_items: Array<number>;
  default_items_quote: number;
  report_template: number;
  type: string;
  pre_procedure_instructions: string;
  consent: string;
}
