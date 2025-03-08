import { Controller } from '@hotwired/stimulus';

/*
TODO:
  - support multiple files
  - previews for non-image files
  - ability to clear/remove files
 */
export default class extends Controller {
  connect() {
    const input = this.element.querySelector('input');
    const dropzone = this.element.querySelector('.dropzone');

    dropzone.addEventListener('dragenter', () => this.element.classList.add('drag'));
    dropzone.addEventListener('dragleave', () => this.element.classList.remove('drag'));
    dropzone.addEventListener('dragover', (e) => e.preventDefault());
    dropzone.addEventListener('drop', (e) => this.handleDrop(e, input));
    dropzone.addEventListener('click', () => input.click());

    input.addEventListener('change', this.handleInputChange.bind(this));
  }

  handleDrop(e, input) {
    e.preventDefault();
    input.files = e.dataTransfer.files;

    const files = [...e.dataTransfer.files];
    this.updatePreview(files);

    this.element.classList.remove('drag');
  }

  handleInputChange(e) {
    const files = e.target.files;
    this.updatePreview(files);
  }

  updatePreview(files) {
    if (files.length !== 1) {
      return;
    }

    const previewImage = this.element.querySelector('.preview img');
    previewImage.src = URL.createObjectURL(files[0]);
    previewImage.classList.remove('d-none');

    const missing = this.element.querySelector('.preview-missing');
    if (missing !== null) {
      missing.classList.add('d-none');
    }
  }
}
