import { Controller } from '@hotwired/stimulus';
import { QuillEditor } from '../../components/QuillEditor';
import { Quote } from '../../components/blots/Quote';
import { QUOTE_EVENT } from '../RichTextEditor';

export class CommentEditor extends Controller {
  static targets = ['editButton', 'editorContainer'];
  static values = {
    updateUrl: String,
  };

  initialize() {
    this.isEditing = false;
    this.editor = null;

    window.setTimeout(this._scrollToSelected.bind(this), 250);
  }

  _scrollToSelected() {
    if (!this.element.classList.contains('selected')) {
      return;
    }

    const rect = this.element.getBoundingClientRect();
    const scrollTop = window.scrollY || document.documentElement.scrollTop;

    window.scrollTo({
      top: rect.top + scrollTop - 150,
      behavior: 'smooth',
    });
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

  async quote(event) {
    const response = await fetch(event.params.url);
    if (!response.ok) {
      return;
    }

    const container = document.createElement('div');
    container.innerHTML = await response.text();

    const quote = container.querySelector(`blockquote.${Quote.className}`);
    if (quote === null) {
      return;
    }

    window.dispatchEvent(new CustomEvent(QUOTE_EVENT, { detail: { html: quote.innerHTML } }));
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
