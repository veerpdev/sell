import IScheduleFee from "./IScheduleFee";
export default interface IScheduleItem {
  id: number;
  name: string;
  description: string;
  amount: number;
  mbs_item_code: string;
  internal_code: string;
  organization_id: number;
  schedule_fees: Array<IScheduleFee>;
}
