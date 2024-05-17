import { Controller } from '@hotwired/stimulus';
import { createPopper } from '@popperjs/core';

const END_ADORNMENT_CLASSES = {
  top: {
    closed: 'ph-caret-up',
    open: 'ph-caret-down',
  },
  left: {
    closed: 'ph-caret-left',
    open: 'ph-caret-right',
  },
  bottom: {
    closed: 'ph-caret-down',
    open: 'ph-caret-up',
  },
  right: {
    closed: 'ph-caret-right',
    open: 'ph-caret-left',
  },
};

export default class extends Controller {
  static values = {
    placement: String,
    hideAdornment: Boolean,
  };
  static targets = ['openButton', 'menu'];

  initialize() {
    super.initialize();

    this.isOpen = false;
    this.placement = this.placementValue || 'bottom';
    this.endAdornment = this.initEndAdornment();
  }

  initEndAdornment() {
    const position = this.placement.split('-')[0];
    this.icons = END_ADORNMENT_CLASSES[position];

    const endAdornment = document.createElement('i');
    endAdornment.classList.add('end-adornment', 'ph', this.icons.closed);

    if (this.hideAdornmentValue) {
      endAdornment.classList.add('d-none');
    }

    this.openButtonTarget.append(endAdornment);
    return endAdornment;
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

    this.endAdornment.classList.remove(this.icons.closed);
    this.endAdornment.classList.add(this.icons.open);
  }

  close() {
    this.menuTarget.style.display = 'none';
    this.isOpen = false;
    this.popper.update();

    this.endAdornment.classList.remove(this.icons.open);
    this.endAdornment.classList.add(this.icons.closed);
  }
}
