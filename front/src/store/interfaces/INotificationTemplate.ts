export default interface INotificationTemplate {
  id: number;
  organization_id: number;
  allow_day_edit: boolean;
  days_before: number;
  description: string;
  email_print_template: string;
  sms_template: string;
  status: string;
  subject: string;
  title: string;
  type: string;
}
