import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['widgetCategorySelect', 'builderRoot', 'widget'];
  static values = {
    pageId: Number,
  }

  initialize() {
    this.twigInput = document.getElementById('page_twig');
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

    this.widgetTargets.forEach((widget) => {
      widget.addEventListener('dragstart', this._dragStart.bind(this));
    });

    this._buildWidgets();
  }

  _persistWidgets() {
    const traverseWidget = (element) => {
      const widget = element.getAttribute('data-widget');
      if (!widget) {
        return null;
      }

      const slots = [...element.querySelectorAll('.widget-slot')]
        .filter((slot) => slot.closest('[data-widget]') === element)
        .map(traverse);

      return {
        widget,
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

    const build = (slot) => (widget) => {
      const prototype = document.querySelector(`#widgets [data-widget="${widget.widget}"]`);
      if (!prototype) {
        return;
      }

      const widgetElement = this._createWidget(slot, prototype.outerHTML);
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
    e.dataTransfer.setData('text/html', e.target.outerHTML);
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
    return (e) => {
      this._dragEnd(e);
      e.stopPropagation(e);

      const widgetHtml = e.dataTransfer.getData('text/html');
      this._createWidget(slot, widgetHtml);
    }
  }

  _createWidget(slot, widgetHtml) {
    const prototype = this._createElementFromHtml(widgetHtml);
    const widget = prototype.firstElementChild;
    widget.classList.add('widget');
    widget.dataset.widget = prototype.dataset.widget;

    const options = prototype.querySelector('.widget-options');
    widget.append(options);

    options.querySelector('.remove').addEventListener('click', () => {
      this._remove(widget);
    });

    this._registerSlots(widget);

    const dropzone = slot.querySelector(':scope > .dropzone');
    dropzone.before(widget);

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
}
