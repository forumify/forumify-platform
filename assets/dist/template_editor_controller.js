import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
  static targets = ['codeEditor'];

  async initialize() {
    this.component = await getComponent(this.element);
  }

  connect() {
    this.handleSave = this._handleSave.bind(this);
    document.addEventListener('keydown', this.handleSave);
  }

  disconnect() {
    document.removeEventListener('keydown', this.handleSave);
  }

  _handleSave(e) {
    if (!((e.metaKey || e.ctrlKey) && e.code === 'KeyS')) {
      return;
    }
    e.preventDefault();
    const content = this.codeEditorTarget.value;
    if (content) {
      this.component.action('saveContent', { content });
    }
  }
}
