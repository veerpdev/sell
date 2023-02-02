import IDocumentSection from "./IDocumentSection";

export default interface IDocumentTemplate {
  id: number;
  title: string;
  type: string;
  sections: Array<IDocumentSection>;
}
