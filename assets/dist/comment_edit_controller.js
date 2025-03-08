import { Controller } from '@hotwired/stimulus';
import RichTextEditor from './src/rich_text_editor';

export default class extends Controller {
  static targets = ['editButton', 'editorContainer'];
  static values = {
    updateUrl: String,
  };

  initialize() {
    this.isEditing = false;
    this.editor = null;
  }

  toggleEdit() {
    (this.isEditing ? this.discardEdit : this.enableEdit).bind(this)();
  }

  enableEdit() {
    this.isEditing = true;

    const content = this.element.querySelector('.rich-text');
    content.classList.add('d-none');

    const editor = document.createElement('div');
    editor.innerHTML = content.innerHTML;
    this.editorContainerTarget.prepend(editor);

    this.editor = RichTextEditor(editor);

    this.editorContainerTarget.classList.remove('d-none');
  }

  discardEdit() {
    this.isEditing = false;

    this.editorContainerTarget.classList.add('d-none');
    const toRemove = this.editorContainerTarget.querySelectorAll('[class^="ql-"]');
    for (const child of toRemove) {
      child.remove();
    }

    const content = this.element.querySelector('.rich-text');
    content.classList.remove('d-none');
  }

  async save() {
    const res = await fetch(this.updateUrlValue, { method: 'POST', body: this.editor.root.innerHTML });
    const newContent = await res.text();

    const richText = this.element.querySelector('.rich-text');
    richText.innerHTML = newContent;
    this.discardEdit();
  }
}
