import { Controller } from '@hotwired/stimulus';
import { QuillEditor } from '../../components/QuillEditor';

export class CommentEditor extends Controller {
  static targets = ['editButton', 'editorContainer'];
  static values = {
    updateUrl: String,
  };

  initialize() {
    this.isEditing = false;
    this.editor = null;

    if (this.element.classList.contains('selected')) {
      const rect = this.element.getBoundingClientRect();
      const scrollTop = window.scrollY || document.documentElement.scrollTop;

      window.scrollTo({
        top: rect.top + scrollTop - 150,
        behavior: 'smooth'
      });
    }
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

    this.editor = QuillEditor(editor);

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

  copyUrl(event) {
    navigator.clipboard.writeText(event.params.url);

    let target = event.target;
    if (target.nodeName !== 'I') {
      target = target.querySelector('i');
    }

    target.classList.remove('ph-link');
    target.classList.add('ph-check');

    window.setTimeout(() => {
      target.classList.add('ph-link');
      target.classList.remove('ph-check');
    }, 3500);
  }
}
