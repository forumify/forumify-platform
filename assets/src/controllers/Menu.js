import { Controller } from '@hotwired/stimulus';
import { createPopper } from '@popperjs/core';

const END_ADORNMENT_CLASSES = {
  top: 'ph-caret-up',
  left: 'ph-caret-left',
  bottom: 'ph-caret-down',
  right: 'ph-caret-right',
};

export class Menu extends Controller {
  static values = {
    placement: String,
    hideAdornment: Boolean,
  };

  static targets = ['openButton', 'menu'];

  initialize() {
    super.initialize();

    this.isOpen = false;
    this.placement = this.placementValue || 'bottom';

    this.initEndAdornment();
  }

  initEndAdornment() {
    const position = this.placement.split('-')[0];
    const icon = END_ADORNMENT_CLASSES[position];

    const endAdornment = document.createElement('i');
    endAdornment.classList.add('end-adornment', 'ph', icon);

    if (this.hideAdornmentValue) {
      endAdornment.classList.add('d-none');
    }

    this.openButtonTarget.append(endAdornment);
  }

  connect() {
    this.popper = createPopper(this.openButtonTarget, this.menuTarget, {
      placement: this.placement,
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
}
