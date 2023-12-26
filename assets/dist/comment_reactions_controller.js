import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
  async initialize() {
    this.component = await getComponent(this.element);
  }
}
