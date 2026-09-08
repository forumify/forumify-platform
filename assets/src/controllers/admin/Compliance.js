import { Controller } from '@hotwired/stimulus';

export class Compliance extends Controller {
  static targets = ['mode', 'section'];

  connect() {
    this.update();
  }

  update() {
    for (const section of this.sectionTargets) {
      section.classList.toggle('d-none', section.dataset.complianceMode !== this.modeTarget.value);
    }
  }
}
