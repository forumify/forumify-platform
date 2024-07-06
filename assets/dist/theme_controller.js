import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  connect() {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (prefersDark) {
      this.element.href += '&preference=dark'
    }
  }
}
