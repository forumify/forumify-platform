import { Controller } from '@hotwired/stimulus';

export class Accordion extends Controller {
  connect() {
    for (const item of this.element.children) {
      const header = item.querySelector('[data-accordion-header]');
      const panel = item.querySelector('[data-accordion-panel]');

      panel.classList.add('accordion-panel', 'd-none');
      header.classList.add('accordion-header');

      const caret = document.createElement('i');
      caret.className = 'end-adornment ph ph-caret-down accordion-caret';
      header.appendChild(caret);

      header.addEventListener('click', () => this.handleHeaderClicked(header, panel));
    }
  }

  closeAll() {
    for (const item of this.element.children) {
      const header = item.querySelector('[data-accordion-header]');
      const panel = item.querySelector('[data-accordion-panel]');
      header?.classList.remove('active');
      panel?.classList.add('d-none');
    }
  }

  handleHeaderClicked(header, panel) {
    const isOpen = !panel.classList.contains('d-none');
    this.closeAll();

    if (!isOpen) {
      header.classList.add('active');
      panel.classList.remove('d-none');
    }
  }
}
