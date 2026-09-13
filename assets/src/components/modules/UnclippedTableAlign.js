import { TableAlign } from 'quill-table-up';

export class UnclippedTableAlign extends TableAlign {
  buildTools() {
    const alignBox = super.buildTools();
    this.quill.container.appendChild(alignBox);
    return alignBox;
  }
}
