import { Controller } from '@hotwired/stimulus';
import { createPopper } from '@popperjs/core';

export default class extends Controller {
  static targets = ['openButton', 'menu'];

  initialize() {
    super.initialize();
    this.isOpen = false;
  }

  open() {
    this.menuTarget.style.display = 'flex';
    this.isOpen = true;
    this.popper.update();
  }

  close() {
    this.menuTarget.style.display = 'none';
    this.isOpen = false;
    this.popper.update();
  }

  connect() {
    this.popper = createPopper(this.openButtonTarget, this.menuTarget, {
      placement: this.element.dataset.placement || 'bottom',
    });

    this.openButtonTarget.addEventListener('click', () => {
      (this.isOpen ? this.close.bind(this) : this.open.bind(this))();
    });

    document.addEventListener('click', (event) => {
      if (!this.isOpen) {
        return;
      }

      if (!this.element.contains(event.target)) {
        this.close();
      }
    });
  }
}
