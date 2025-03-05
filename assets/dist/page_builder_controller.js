import { Controller } from '@hotwired/stimulus';

const mutatePath = (o, pth, value) => {
  const [x, ...xs] = pth;
  if (xs.length === 0) {
    o[x] = value;
    return;
  }

  if (o[x] === undefined) {
    o[x] = {};
  }

  return mutatePath(o[x], xs, value);
};

const formToData = (form) => {
  const data = {};
  [...(new FormData(form)).entries()].forEach(([key, value]) => {
    const path = key
      .replace(/^form/, '')
      .replace(/\[|\]/g, '.')
      .split('.')
      .filter(Boolean);

    mutatePath(data, path, value);
  });
  return data;
}

// TODO: this is actually just vile...
export default class extends Controller {
  static targets = ['widgetCategorySelect', 'previewToggle', 'builderRoot', 'widget'];
  static values = {
    pageId: Number,
  }

  initialize() {
    this.settingsFormIncrement = 0;
    this.twigInput = document.getElementById('page_twig');
    this.lastDragElement = null;
  }

  connect() {
    const form = document.querySelector('form[name="page"]');
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      this._persistWidgets();
      form.submit();
    });

    this.widgetCategorySelectTarget.addEventListener('change', this._selectCategory.bind(this));
    this._selectCategory();

    this.previewToggleTarget.addEventListener('change', this._togglePreview.bind(this));
    this._togglePreview();

    this.widgetTargets.forEach((widget) => {
      widget.addEventListener('dragstart', this._dragStart.bind(this));
    });

    this._buildWidgets();
  }

  _persistWidgets() {
    const traverseWidget = (element) => {
      const widget = element.dataset.widget;
      if (!widget) {
        return null;
      }

      const slots = [...element.querySelectorAll('.widget-slot')]
        .filter((slot) => slot.closest('[data-widget]') === element)
        .map(traverse);

      let settings = {};
      if (element.dataset.settingsForm) {
        const form = document.getElementById(element.dataset.settingsForm);
        settings = formToData(form);
      }

      return {
        widget,
        settings,
        ...(slots.length && { slots }),
      };
    };

    const traverse = (el) => [...el.children]
      .map(traverseWidget)
      .filter((el) => el !== null);

    const rootSlot = this.builderRootTarget.querySelector('.widget-slot');
    const tree = traverse(rootSlot);
    this.twigInput.value = JSON.stringify(tree);
  }

  _buildWidgets() {
    this._registerSlots(this.builderRootTarget);
    let tree;
    try {
      tree = JSON.parse(this.twigInput.value);
    } catch (e) {
      tree = [];
    }

    const build = (slot) => async (widget) => {
      const prototype = document.querySelector(`#widgets [data-widget="${widget.widget}"]`);
      if (!prototype) {
        return;
      }

      const widgetElement = await this._createWidget(prototype.outerHTML, widget.settings || {});
      const dropzone = slot.querySelector(':scope > .dropzone');
      dropzone.before(widgetElement);

      widgetElement.querySelectorAll('.widget-slot').forEach((slot, i) => {
        const builder = build(slot);
        (widget.slots[i] || []).forEach((slotWidget) => {
          builder(slotWidget);
        });
      });
    }

    const rootSlot = this.builderRootTarget.querySelector('.widget-slot');
    tree.forEach(build(rootSlot));
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

    slot.append(dropzone)
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

  _drop (slot) {
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
    }
  }

  async _createWidget(widgetHtml, settings = {}) {
    const widget = this._createElementFromHtml(widgetHtml);
    widget.addEventListener('dragstart', (e) => {
      this._dragStart(e);
      e.dataTransfer.setData('application/x-relocate', 'true');
    });
    widget.querySelector('.remove')?.addEventListener('click', () => {
      this._remove(widget);
    });

    const settingsBtn = widget.querySelector('.settings');
    const hasForm = !!widget.querySelector('form');
    if (settingsBtn && !hasForm) {
      const formId = await this._createSettingsForm(widget, settings, settingsBtn);
      widget.dataset.settingsForm = formId;
    }

    this._registerSlots(widget);
    return widget;
  }

  _createElementFromHtml(html) {
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    return wrapper.firstElementChild;
  }

  _remove(widget) {
    widget.parentElement.removeChild(widget);
  }

  async _createSettingsForm(widget, settings, settingsBtn) {
    const widgetName = widget.dataset.widget;
    const settingsFormHtml = await fetch(`/admin/cms/pagebuilder/settings?widget=${widgetName}`, {
      method: 'post',
      body: JSON.stringify(settings),
    }).then((res) => res.text());
    const settingsModal = this._createElementFromHtml(settingsFormHtml);
    document.body.append(settingsModal);

    const settingsForm = settingsModal.querySelector('form');
    const formId = 'settings-form-' + this._getNextSettingFormId();
    settingsForm.id = formId;
    settingsForm.addEventListener('submit', (e) => {
      e.preventDefault();
    });
    settingsForm.submit = () => {
      const settings = formToData(settingsForm);
      this._hydrateWidget(widget, settings);

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

  _getNextSettingFormId() {
    this.settingsFormIncrement++;
    return this.settingsFormIncrement;
  }

  _hydrateWidget(widget, settings) {
    Object.entries(settings).forEach(([setting, value]) => {
      if (!value) {
        return;
      }

      const settingDataAttr = `data-setting-${setting}`;
      [...widget.querySelectorAll(`[${settingDataAttr}]`)]
        .filter((e) => e.closest('[data-widget]') === widget)
        .forEach((e) => {
          const attr = e.getAttribute(settingDataAttr);
          e[attr] = value;
        });
    });
  }
}
