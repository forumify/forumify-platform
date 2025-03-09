import { Controller } from '@hotwired/stimulus';
import {
  formToData,
  widgetHtmlToObject,
  hydrateWidget,
  createAutoIncrement,
  createElementFromHtml,
} from './src/page_builder';

export default class extends Controller {
  static targets = ['widgetCategorySelect', 'previewToggle', 'builderRoot', 'loader', 'widget'];
  static values = {
    settingsEndpoint: String,
  };

  initialize() {
    this.settingsFormIncrement = createAutoIncrement();
    this.twigInput = document.getElementById('page_twig');
    this.pageForm = document.querySelector('form[name="page"]');
    this.lastDragElement = null;
  }

  connect() {
    this.widgetCategorySelectTarget.addEventListener('change', this._selectCategory.bind(this));
    this._selectCategory();

    this.previewToggleTarget.addEventListener('change', this._togglePreview.bind(this));
    this._togglePreview();

    this.widgetTargets.forEach((widget) => {
      widget.addEventListener('dragstart', this._dragStart.bind(this));
    });

    this.pageForm.addEventListener('submit', (e) => {
      e.preventDefault();
      this._persistWidgets();
      e.target.submit();
    });

    this._buildWidgets();
  }

  _persistWidgets() {
    const tree = widgetHtmlToObject(this.builderRootTarget);
    this.twigInput.value = JSON.stringify(tree);
  }

  async _buildWidgets() {
    this.builderRootTarget.classList.add('d-none');
    this.loaderTarget.classList.remove('d-none');

    this._registerSlots(this.builderRootTarget);
    const tree = JSON.parse(this.twigInput.value);

    const rootSlot = this.builderRootTarget.querySelector('.widget-slot');
    await this._buildSlot(rootSlot, tree)

    this.loaderTarget.classList.add('d-none');
    this.builderRootTarget.classList.remove('d-none');
  }

  async _buildSlot(slot, widgets) {
    const widgetElements = await Promise.all(widgets.map(async (widget) => {
      const prototype = document.querySelector(`#widgets [data-widget="${widget.widget}"]`);
      if (!prototype) {
        return null;
      }

      const widgetElement = await this._createWidget(prototype.outerHTML, widget.settings || {});
      const slotBuilders = [...widgetElement.querySelectorAll('.widget-slot')].map((slot, i) => this._buildSlot(slot, widget.slots[i] || []));
      await Promise.all(slotBuilders);

      return widgetElement;
    }));

    const dropzone = slot.querySelector(':scope > .dropzone');
    widgetElements
      .filter((el) => el !== null)
      .forEach((el) => dropzone.before(el));
  }

  _selectCategory() {
    const select = this.widgetCategorySelectTarget;
    this.element.querySelectorAll('.widget-category').forEach((el) => el.classList.add('d-none'));
    document.getElementById(`widgets-${select.value}`).classList.remove('d-none');
  }

  _togglePreview() {
    const checked = this.previewToggleTarget.checked;
    if (checked) {
      this.builderRootTarget.classList.add('preview');
    } else {
      this.builderRootTarget.classList.remove('preview');
    }
  }

  _registerSlots(element) {
    element.querySelectorAll('.widget-slot').forEach((slot) => {
      this._insertDropzone(slot);
    });
  }

  _insertDropzone(slot) {
    const dropzone = document.createElement('div');
    dropzone.classList.add('dropzone');
    dropzone.addEventListener('dragover', this._dragOver.bind(this));
    dropzone.addEventListener('dragleave', this._dragEnd.bind(this));
    dropzone.addEventListener('drop', this._drop(slot).bind(this));

    slot.append(dropzone);
  }

  _dragStart(e) {
    this.lastDragElement = e.target;
    e.dataTransfer.effectAllowed = 'copy';
  }

  _dragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    e.target.classList.add('dragover');
  }

  _dragEnd(e) {
    e.preventDefault();
    e.target.classList.remove('dragover');
  }

  _drop(slot) {
    return async (e) => {
      const isRelocate = !!e.dataTransfer?.getData('application/x-relocate');
      this._dragEnd(e);
      e.stopPropagation(e);

      if (!this.lastDragElement) {
        return;
      }

      const widget = isRelocate
        ? this.lastDragElement
        : await this._createWidget(this.lastDragElement.outerHTML);

      const dropzone = slot.querySelector(':scope > .dropzone');
      dropzone.before(widget);
    };
  }

  async _createWidget(widgetHtml, settings = {}) {
    const widget = createElementFromHtml(widgetHtml);
    widget.addEventListener('dragstart', (e) => {
      this._dragStart(e);
      e.dataTransfer.setData('application/x-relocate', 'true');
    });
    widget.querySelector('.remove')?.addEventListener('click', () => {
      widget.remove();
    });

    const settingsBtn = widget.querySelector('.settings');
    const hasForm = !!widget.querySelector('form');
    if (settingsBtn && !hasForm) {
      const formId = await this._createSettingsForm(widget, settings, settingsBtn);
      if (formId) {
        widget.dataset.settingsForm = formId;
      }
    }

    this._registerSlots(widget);
    return widget;
  }

  async _createSettingsForm(widget, settings, settingsBtn) {
    const widgetName = widget.dataset.widget;
    const settingsFormHtml = await fetch(`${this.settingsEndpointValue}?widget=${widgetName}`, {
      method: 'post',
      body: JSON.stringify(settings),
    }).then((res) => res.text());

    if (!settingsFormHtml) {
      return null;
    }

    const settingsModal = createElementFromHtml(settingsFormHtml);
    document.body.append(settingsModal);

    const settingsForm = settingsModal.querySelector('form');
    const formId = 'settings-form-' + this.settingsFormIncrement();
    settingsForm.id = formId;
    settingsForm.addEventListener('submit', (e) => {
      e.preventDefault();
      settingsForm.submit();
    });
    settingsForm.submit = () => {
      const settings = formToData(settingsForm);
      hydrateWidget(widget, settings);

      settingsModal.classList.remove('open');
    };
    settingsForm.submit();

    const submit = settingsModal.querySelector('.close-settings');
    submit.addEventListener('click', () => {
      settingsForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    });

    settingsBtn.addEventListener('click', () => settingsModal.classList.add('open'));
    return formId;
  }
}
