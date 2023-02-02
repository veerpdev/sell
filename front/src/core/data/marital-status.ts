interface IMaritalStatus {
  value: string;
  label: string;
}

export const chargeTypes: Array<IMaritalStatus> = [
  { value: "1", label: "Single" },
  { value: "2", label: "Married" },
  { value: "3", label: "Not Stated" },
  { value: "4", label: "Divorced" },
  { value: "5", label: "Widowed" },
  { value: "6", label: "Never married" },
  { value: "7", label: "Separated" },
  { value: "9", label: "De Facto" },
];

export { IMaritalStatus };

export default chargeTypes;
