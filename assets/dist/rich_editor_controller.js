import { Controller } from '@hotwired/stimulus';
import RichTextEditor from './src/rich_text_editor';

export default class extends Controller {
  quill = null;

  initialize() {
    const editor = this.element.querySelector('#editor');
    this.quill = RichTextEditor(editor);
  }

  connect() {
    const input = this.element.querySelector('textarea');
    this.quill.on('text-change', () => {
      input.value = this.quill.getSemanticHTML();
    });
  }

  disconnect() {
    this.quill.setContents([{ insert: '' }]);
    this.quill.off('text-change');
  }
}
