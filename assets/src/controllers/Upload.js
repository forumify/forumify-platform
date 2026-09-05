import { Controller } from '@hotwired/stimulus';

const UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

export class Upload extends Controller {
  static targets = ['input', 'removed', 'dropzone', 'list', 'template'];

  static values = {
    multiple: Boolean,
  };

  connect() {
    this.pending = new DataTransfer();
    this.removedPaths = [];
    this.dragDepth = 0;

    this.dropzoneTarget.addEventListener('click', () => this.inputTarget.click());
    this.dropzoneTarget.addEventListener('keydown', (e) => this.onKeydown(e));
    this.dropzoneTarget.addEventListener('dragenter', (e) => this.onDragEnter(e));
    this.dropzoneTarget.addEventListener('dragleave', () => this.onDragLeave());
    this.dropzoneTarget.addEventListener('dragover', (e) => e.preventDefault());
    this.dropzoneTarget.addEventListener('drop', (e) => this.onDrop(e));

    this.inputTarget.addEventListener('change', () => this.addFiles(this.inputTarget.files));
    this.inputTarget.addEventListener('focus', () => this.dropzoneTarget.classList.add('focus'));
    this.inputTarget.addEventListener('blur', () => this.dropzoneTarget.classList.remove('focus'));
  }

  disconnect() {
    this.listTarget.querySelectorAll('img[data-object-url]').forEach((img) => URL.revokeObjectURL(img.src));
  }

  onKeydown(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      this.inputTarget.click();
    }
  }

  onDragEnter(e) {
    e.preventDefault();
    this.dragDepth++;
    this.element.classList.add('drag');
  }

  onDragLeave() {
    this.dragDepth--;
    if (this.dragDepth <= 0) {
      this.dragDepth = 0;
      this.element.classList.remove('drag');
    }
  }

  onDrop(e) {
    e.preventDefault();
    this.dragDepth = 0;
    this.element.classList.remove('drag');
    this.addFiles(e.dataTransfer.files);
  }

  addFiles(files) {
    if (!this.multipleValue) {
      this.pending = new DataTransfer();
    }

    [...files].forEach((file) => this.pending.items.add(file));

    this.inputTarget.files = this.pending.files;
    this.render();
  }

  removePending(e) {
    const row = e.currentTarget.closest('.upload-file');
    const index = [...this.listTarget.querySelectorAll('.upload-file-new')].indexOf(row);
    if (index < 0) {
      return;
    }

    this.pending.items.remove(index);
    this.inputTarget.files = this.pending.files;
    this.render();
  }

  removeExisting(e) {
    const row = e.currentTarget.closest('.upload-file');
    this.removedPaths.push(row.dataset.path);
    this.removedTarget.value = this.removedPaths.join(',');
    row.remove();
  }

  render() {
    this.listTarget.querySelectorAll('.upload-file-new').forEach((row) => {
      const img = row.querySelector('img[data-object-url]');
      if (img) {
        URL.revokeObjectURL(img.src);
      }
      row.remove();
    });

    const files = [...this.pending.files];
    files.forEach((file) => this.listTarget.appendChild(this.createRow(file)));

    // A single-file upload replaces what is already there, so stop showing the old file.
    const replaced = !this.multipleValue && files.length > 0;
    this.listTarget.querySelectorAll('.upload-file[data-path]').forEach((row) => {
      row.hidden = replaced;
    });
  }

  createRow(file) {
    const row = this.templateTarget.content.firstElementChild.cloneNode(true);
    row.querySelector('.upload-name').textContent = file.name;
    row.querySelector('.upload-name').title = file.name;
    row.querySelector('.upload-sub').textContent = this.formatSize(file.size);

    const img = row.querySelector('img');
    const ext = row.querySelector('.upload-ext');

    if (file.type.startsWith('image/')) {
      img.src = URL.createObjectURL(file);
      img.dataset.objectUrl = '';
      img.hidden = false;
      ext.remove();
    } else {
      img.remove();
      ext.textContent = file.name.split('.').pop().toUpperCase().slice(0, 4);
    }

    return row;
  }

  formatSize(bytes) {
    if (!bytes) {
      return '';
    }

    const unit = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), UNITS.length - 1);
    const size = bytes / 1024 ** unit;

    return `${size.toFixed(size < 10 && unit > 0 ? 1 : 0)} ${UNITS[unit]}`;
  }
}
