/**
 * @param {HTMLFormElement} form
 * @returns {Object}
 */
export const formToData = (form) => {
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

export const createAutoIncrement = () => {
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
export const widgetHtmlToObject = (rootElement) => {
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
export const hydrateWidget = (widget, settings) => {
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
export const createElementFromHtml = (html) => {
  const wrapper = document.createElement('div');
  wrapper.innerHTML = html;
  return wrapper.firstElementChild;
};
