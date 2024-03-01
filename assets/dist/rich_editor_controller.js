import { Controller } from '@hotwired/stimulus';
import Quill from 'quill';
import 'quill/dist/quill.snow.css'

export default class extends Controller {
  /**
   * @type {null|Quill}
   */
  quill = null;

  initialize() {
    this.quill = new Quill(this.element.querySelector('#editor'), {
      modules: {
        toolbar: [
          [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
          ['bold', 'italic', 'underline'],
          [{ 'size': ['small', false, 'large', 'huge'] }],
          [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'align': [] }],
          ['blockquote', 'code-block'],
          ['link', 'image'],
          [{ 'color': [] }, { 'background': [] }],
          ['clean']
        ],
      },
      theme: 'snow',
    });
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
