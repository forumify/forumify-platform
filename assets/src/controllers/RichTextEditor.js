import { Controller } from '@hotwired/stimulus';
import Quill from 'quill';
import { QuillEditor } from '../components/QuillEditor';
import { Quote } from '../components/blots/Quote';
import { uploadEmbeddedImages } from '../services/media';

export const QUOTE_EVENT = 'forumify:quote';

export class RichTextEditor extends Controller {
  quill = null;

  initialize() {
    const editor = this.element.querySelector('#editor');
    this.quill = QuillEditor(editor);
    this.input = this.element.querySelector('textarea');
  }

  connect() {
    this.onQuote = this.insertQuote.bind(this);
    window.addEventListener(QUOTE_EVENT, this.onQuote);

    const form = this.getParentForm();

    if (!form) {
      // could not find form, we have to update the input any time the editor changes.
      this.quill.on('text-change', () => {
        this.input.value = this.quill.getSemanticHTML();
      });

      return;
    }

    this.isRequired = this.input.required;
    this.input.required = false;

    form.addEventListener('submit', this.handleFormSubmit.bind(this));
  }

  disconnect() {
    window.removeEventListener(QUOTE_EVENT, this.onQuote);
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

  handleFormSubmit(e) {
    e.preventDefault();
    const value = this.quill.getSemanticHTML();
    if (this.isRequired && value === '<p></p>') {
      return;
    }

    uploadEmbeddedImages(value)
      .then((transformedValue) => {
        this.input.value = transformedValue;
      })
      .finally(() => {
        e.target.submit();
      });
  }
}
