import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  connect() {
    this.element.querySelectorAll('.mention').forEach((mention) => {
      mention.addEventListener('click', () => {
        location.href = `/profile/${mention.dataset.id}`
      });
    });
  }
}
