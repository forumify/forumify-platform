import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['firstItem'];

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

  firstItemTargetConnected(element) {
    this.observer ??= new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.dispatchEvent(new CustomEvent('appear', { detail: { entry } }));
        }
      });
    });
    this.observer.observe(element);
  }

  firstItemTargetDisconnected(element) {
    this.observer?.unobserve(element);
  }
}
