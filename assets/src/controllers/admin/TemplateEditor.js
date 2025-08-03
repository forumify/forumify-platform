import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export class TemplateEditor extends Controller {
  static targets = ['codeEditor'];

  async initialize() {
    try {
      this.component = await getComponent(this.element);
    } catch (e) {
      console.error('TemplateEditor live component not found, modifications to templates will not be stored!', e);
      this.component = null;
    }
  }

  connect() {
    this.savedContent = null;
    this.handleSave = this._handleCtrlS.bind(this);
    document.addEventListener('keydown', this.handleSave);
  }

  disconnect() {
    document.removeEventListener('keydown', this.handleSave);
  }

  codeEditorTargetConnected(element) {
    this.savedContent = element.innerText;
    this.saveDot = this.element.querySelector('.dot');

    element.addEventListener('change', (e) => {
      if (e.target.value !== this.savedContent) {
        this.saveDot?.classList.remove('d-none');
      } else {
        this.saveDot?.classList.add('d-none');
      }
    });
  }

  _handleCtrlS(e) {
    if (!((e.metaKey || e.ctrlKey) && e.code === 'KeyS')) {
      return;
    }
    e.preventDefault();
    this.save();
  }

  save() {
    const content = this.codeEditorTarget.value;
    if (this.component && content) {
      this.component.action('saveContent', { content });
    }

    this.savedContent = content;
    this.saveDot?.classList.add('d-none');
  }
}
