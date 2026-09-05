import { Controller } from '@hotwired/stimulus';
import Quill from 'quill';
import { QuillEditor } from '../components/QuillEditor';
import { Quote } from '../components/blots/Quote';

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

    this.preSubmitTransformValue(value)
      .then((transformedValue) => {
        this.input.value = transformedValue;
      })
      .finally(() => {
        e.target.submit();
      });
  }

  async preSubmitTransformValue(value) {
    const dom = document.createElement('div');
    dom.innerHTML = value;

    const uploads = [];
    for (const img of dom.querySelectorAll('img')) {
      if (img.src.startsWith('data:')) {
        uploads.push(this.handleDataUrlImg(img));
      }
    }

    await Promise.all(uploads);
    return dom.innerHTML;
  }

  /**
   * @param {HTMLImageElement} imgElement
   */
  async handleDataUrlImg(imgElement) {
    const [meta, b64data] = imgElement.src.split(',');
    const type = meta.match(/:(.*?);/)[1];
    const ext = type.split('/')[1];

    const binData = atob(b64data);

    const bytes = new Uint8Array(binData.length);
    for (let i = 0; i < binData.length; i++) {
      bytes[i] = binData.charCodeAt(i);
    }

    const formData = new FormData();
    formData.append('file', new File([bytes], `file.${ext}`, { type }));

    try {
      const response = await fetch('/media/upload', {
        method: 'post',
        body: formData,
      });

      const data = await response.json();
      if (data.url) {
        imgElement.src = data.url;
      } else if (data.error) {
        console.error(data.error);
      }
    } catch (e) {
      console.error(e);
    }
  }
}
