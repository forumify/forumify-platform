import { Controller } from '@hotwired/stimulus';

export class Modal extends Controller {
  static targets = ['modal', 'modalBody'];

  connect() {
    this.element.firstElementChild.addEventListener('click', this.open.bind(this));

    // click-away listener
    this.modalTarget.addEventListener('click', (e) => {
      if (!this.modalBodyTarget.contains(e.target)) {
        this.close();
      }
    });
  }

  open() {
    this.modalTarget.classList.add('open');
  }

  close() {
    this.modalTarget.classList.remove('open');
  }
}
