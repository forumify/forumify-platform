import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    lastPageFirst: Boolean,
  };

  connect() {
    if (this.lastPageFirstValue) {
      const elementBottom = this.element.getBoundingClientRect().bottom + window.scrollY + 120;
      window.scrollTo({ top: elementBottom - window.innerHeight });
    }
  }

  switchPage() {
    const top = this.element.getBoundingClientRect().top + window.scrollY - 120;
    window.scrollTo({ top, behavior: 'smooth' });
  }
}
