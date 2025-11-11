import { Controller } from '@hotwired/stimulus';

/**
 * @param {HTMLFormElement} form
 * @returns {Object}
 */
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
};

/**
 * @param {Object} o
 * @param {string[]} pth
 * @param {*} value
 */
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

const createAutoIncrement = () => {
  let i = 0;
  return () => {
    i++;
    return i;
  };
};

/**
 * @param {HTMLElement} rootElement
 * @returns {Object[]}
 */
const widgetHtmlToObject = (rootElement) => {
  const widgetToObject = (element) => {
    const widget = element.dataset.widget;
    if (!widget) {
      return null;
    }

    const slots = [...element.querySelectorAll('.widget-slot')]
      .filter(isInCurrentWidget(element))
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
    .map(widgetToObject)
    .filter((el) => el !== null);

  const rootSlot = rootElement.querySelector('.widget-slot');
  return traverse(rootSlot);
};

/**
 * @param {HTMLElement} widget
 * @param {Object} settings
 */
const hydrateWidget = (widget, settings) => {
  Object.entries(settings).forEach(([setting, value]) => {
    if (!value) {
      return;
    }

    const settingDataAttr = `data-setting-${setting}`;
    [...widget.querySelectorAll(`[${settingDataAttr}]`)]
      .filter(isInCurrentWidget(widget))
      .forEach((e) => {
        const attr = e.getAttribute(settingDataAttr);
        e[attr] = value;
      });
  });
};

/**
 * @callback findWidget
 * @param {HTMLElement} element
 * @returns {boolean}
 */
/**
 * @param {HTMLElement} widget
 * @retuns {findWidget}
 */
const isInCurrentWidget = (widget) => (element) => element.closest('[data-widget]') === widget;

/**
 * @param {string} html
 * @returns {HTMLElement}
 */
const createElementFromHtml = (html) => {
  const wrapper = document.createElement('div');
  wrapper.innerHTML = html;
  return wrapper.firstElementChild;
};

export class PageBuilder extends Controller {
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
    let tree;
    try {
      tree = JSON.parse(this.twigInput.value);
    } catch (e) {
      console.error(e);
      tree = [];
    }

    const rootSlot = this.builderRootTarget.querySelector('.widget-slot');
    await this._buildSlot(rootSlot, tree);

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
    const settingsForm = settingsModal.querySelector('form');
    const id = this.settingsFormIncrement();
    const formId = `settings-form-${id}`;
    settingsForm.id = formId;

    settingsForm.querySelectorAll('[id^=form_]').forEach((el) => {
      el.id += `-${id}`;
    });

    settingsForm.querySelectorAll('label').forEach((el) => {
      el.htmlFor += `-${id}`;
    });

    settingsForm.submit = () => {
      const settings = formToData(settingsForm);
      hydrateWidget(widget, settings);

      settingsModal.classList.remove('open');
    };
    settingsForm.submit();

    settingsForm.addEventListener('submit', (e) => {
      e.preventDefault();
      settingsForm.submit();
    });

    const submit = settingsModal.querySelector('.close-settings');
    submit.addEventListener('click', () => {
      settingsForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    });

    settingsBtn.addEventListener('click', () => settingsModal.classList.add('open'));

    document.body.append(settingsModal);
    return formId;
  }
}
