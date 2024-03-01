import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    id: Number,
    content: String,
    updateUrl: String,
  }

  initialize() {
    this.isEditing = false;
  }

  connect() {
    this.element.addEventListener('click', this.enableEdit.bind(this));
  }

  enableEdit() {
    if (this.isEditing) {
      return;
    }

    const prototype = document.getElementById('comment-edit-prototype');
    const contentWrapper = document.querySelector(`#comment-${this.idValue} .comment-content`);
    const originalContent = contentWrapper.querySelector('.markdown');

    originalContent.classList.add('d-none');

    const editor = prototype.cloneNode(true);
    editor.id = '';
    editor.classList.remove('d-none');

    const input = editor.querySelector('textarea');
    input.value = this.contentValue;

    const closeEditor = () => {
      editor.remove();
      originalContent.classList.remove('d-none');
      this.isEditing = false;
    }

    editor.querySelector('#comment-edit-save').addEventListener('click', () => {
      fetch(this.updateUrlValue, { method: 'POST', body: input.value })
        .then((res) => res.text())
        .then((newContent) => {
          contentWrapper.querySelector('.markdown').innerHTML = newContent;
          closeEditor();
        });
    });

    editor.querySelector('#comment-edit-cancel').addEventListener('click', closeEditor);

    this.isEditing = true;
    contentWrapper.appendChild(editor);
  }
}
