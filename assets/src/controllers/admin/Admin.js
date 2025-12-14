import { Controller } from '@hotwired/stimulus';

export class Admin extends Controller {
  connect() {
    if (localStorage.getItem('menu-closed') === 'true') {
      this.toggleNavigation(false);
    }
  }

  toggleNavigation(flipStorage = true) {
    if (flipStorage) {
      const isMenuClosed = localStorage.getItem('menu-closed');
      localStorage.setItem('menu-closed', isMenuClosed === 'true' ? 'false' : 'true');
    }

    this.element.classList.toggle('navigation-closed');

    const themeSelector = this.element.querySelector('.nav-controls .theme-selector');
    themeSelector.classList.toggle('d-none');

    const menuToggle = this.element.querySelector('.nav-controls .menu-toggle');
    menuToggle.classList.toggle('btn-small');
  }
}
