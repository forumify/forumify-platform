import { Controller } from '@hotwired/stimulus';
import { QuillEditor } from '../components/QuillEditor';
import { uploadEmbeddedImages } from '../services/media';

export class RichTextEditor extends Controller {
  quill = null;

  initialize() {
    const editor = this.element.querySelector('#editor');
    this.quill = QuillEditor(editor);
    this.input = this.element.querySelector('textarea');
  }

  connect() {
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
    this.quill.setContents([{ insert: '' }]);
    this.quill.off('text-change');
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
