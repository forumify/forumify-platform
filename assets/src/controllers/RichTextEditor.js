import { Controller } from '@hotwired/stimulus';
import Quill from 'quill';
import { QuillEditor } from '../components/QuillEditor';
import { Quote } from '../components/blots/Quote';
import { uploadEmbeddedImages } from '../services/media';

export const QUOTE_EVENT = 'forumify:quote';

const EMPTY_VALUE = '<p></p>';

const editorsByForm = new WeakMap();

const submitForm = (e) => {
  e.preventDefault();

  const form = e.currentTarget;
  const editors = [...editorsByForm.get(form)];
  const values = editors.map((editor) => editor.quill.getSemanticHTML());

  if (editors.some((editor, i) => editor.isRequired && values[i] === EMPTY_VALUE)) {
    return;
  }

  Promise
    .all(editors.map((editor, i) => uploadEmbeddedImages(values[i]).then((value) => {
      editor.input.value = value;
    })))
    .finally(() => form.submit());
};

const registerEditor = (form, editor) => {
  if (!editorsByForm.has(form)) {
    editorsByForm.set(form, new Set());
    form.addEventListener('submit', submitForm);
  }

  editorsByForm.get(form).add(editor);
};

const unregisterEditor = (form, editor) => {
  const editors = editorsByForm.get(form);
  if (editors === undefined) {
    return;
  }

  editors.delete(editor);
  if (editors.size === 0) {
    editorsByForm.delete(form);
    form.removeEventListener('submit', submitForm);
  }
};

export class RichTextEditor extends Controller {
  quill = null;
  form = null;

  initialize() {
    const editor = this.element.querySelector('#editor');
    this.quill = QuillEditor(editor);
    this.input = this.element.querySelector('textarea');
  }

  connect() {
    this.onQuote = this.insertQuote.bind(this);
    window.addEventListener(QUOTE_EVENT, this.onQuote);

    this.form = this.getParentForm() ?? null;

    if (this.form === null) {
      // could not find form, we have to update the input any time the editor changes.
      this.quill.on('text-change', () => {
        this.input.value = this.quill.getSemanticHTML();
      });

      return;
    }

    this.isRequired = this.input.required;
    this.input.required = false;

    registerEditor(this.form, this);
  }

  disconnect() {
    window.removeEventListener(QUOTE_EVENT, this.onQuote);

    if (this.form !== null) {
      unregisterEditor(this.form, this);
      this.form = null;
    }

    this.quill.setContents([{ insert: '' }]);
    this.quill.off('text-change');
  }

  insertQuote({ detail }) {
    const Delta = Quill.import('delta');
    const index = this.quill.getLength() - 1;
    const [line] = this.quill.getLine(index);

    const delta = new Delta().retain(index);
    if (line !== null && line.length() > 1) {
      delta.insert('\n');
    }
    delta.insert({ [Quote.blotName]: detail.html });

    this.quill.updateContents(delta, 'user');
    this.quill.setSelection(this.quill.getLength() - 1, 0);
    this.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  getParentForm() {
    let element = this.element;
    let form;
    while (form === undefined && element.parentElement) {
      element = element.parentElement;
      if (element.tagName.toLowerCase() === 'form') {
        form = element;
      }
    }
    return form;
  }
}
