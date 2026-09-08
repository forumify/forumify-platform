import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import 'quill-mention/autoregister';
import TableUp, {
  defaultCustomSelect,
  TableAlign,
  TableMenuContextmenu,
  TableResizeLine,
  TableResizeScale,
  TableSelection,
} from 'quill-table-up';
import 'quill-table-up/index.css';
import 'quill-table-up/table-creator.css';
import { Quote } from './blots/Quote';
import { ImageResizer } from './modules/ImageResizer';

Quill.register(Quote);
Quill.register('modules/imageResizer', ImageResizer);
Quill.register({ [`modules/${TableUp.moduleName}`]: TableUp }, true);

/**
 * @param {HTMLElement} element
 * @returns {Quill}
 * @constructor
 */
export const QuillEditor = (element) => {
  const quill = new Quill(element, {
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, 4, 5, 6, false] }],
        ['bold', 'italic', 'underline'],
        [{ size: ['small', false, 'large', 'huge'] }],
        [{ list: 'ordered' }, { list: 'bullet' }, { align: [] }],
        ['blockquote', 'code-block'],
        ['link', 'image', { [TableUp.toolName]: [] }],
        [{ color: [] }, { background: [] }],
        ['clean'],
      ],
      imageResizer: true,
      [TableUp.moduleName]: {
        full: true,
        customSelect: defaultCustomSelect,
        modules: [
          { module: TableAlign },
          { module: TableResizeLine },
          { module: TableResizeScale },
          { module: TableSelection },
          { module: TableMenuContextmenu },
        ],
      },
      mention: {
        allowedChars: /^[A-Za-z\sÅÄÖåäö]*$/,
        mentionDenotationChars: ['@'],
        source: createMentionSource(),
      },
    },
    theme: 'snow',
  });

  const getSemanticHTML = quill.getSemanticHTML.bind(quill);
  quill.getSemanticHTML = (...args) => normalizeWhitespace(getSemanticHTML(...args));

  quill.clipboard.addMatcher(Node.ELEMENT_NODE, (_, delta) => {
    delta.forEach((d) => {
      if (d.attributes) {
        d.attributes.color = '';
        d.attributes.background = '';
      }
    });
    return delta;
  });

  return quill;
};

const NBSP = '\u00a0';

/**
 * Replaces non-breaking spaces with regular spaces, except where HTML would collapse them away.
 *
 * @see https://github.com/slab/quill/issues/4509
 *
 * @param {string} html
 * @returns {string}
 */
const normalizeWhitespace = (html) => {
  const dom = document.createElement('div');
  dom.innerHTML = html;

  const walker = document.createTreeWalker(dom, NodeFilter.SHOW_TEXT);
  for (let node = walker.nextNode(); node !== null; node = walker.nextNode()) {
    if (node.parentElement.closest('pre, code') !== null) {
      node.data = node.data.replaceAll(NBSP, ' ');
      continue;
    }

    node.data = keepOnlyCollapsibleNbsp(node.data, node.previousSibling === null, node.nextSibling === null);
  }

  return dom.innerHTML;
};

/**
 * @param {string} text
 * @param {boolean} protectStart
 * @param {boolean} protectEnd
 * @returns {string}
 */
const keepOnlyCollapsibleNbsp = (text, protectStart, protectEnd) => {
  let result = text
    .replaceAll(NBSP, ' ')
    .replace(/ {2,}/g, (run) => NBSP.repeat(run.length - 1) + ' ');

  if (protectStart && result.startsWith(' ')) {
    result = NBSP + result.slice(1);
  }

  if (protectEnd && result.endsWith(' ')) {
    result = result.slice(0, -1) + NBSP;
  }

  return result;
};

const MENTION_SEARCH_DEBOUNCE = 250;

/**
 * @returns {(searchTerm: string, renderList: (values: Array, searchTerm: string) => void) => void}
 */
const createMentionSource = () => {
  let timeout;
  let controller;

  return (searchTerm, renderList) => {
    clearTimeout(timeout);
    controller?.abort();

    if (searchTerm.length === 0) {
      renderList([], searchTerm);
      return;
    }

    timeout = setTimeout(() => {
      controller = new AbortController();
      fetch('/users/search?query=' + encodeURIComponent(searchTerm), { signal: controller.signal })
        .then((res) => res.json())
        .then((users) => users.map((user) => ({ id: user.id, value: user.displayName || user.username })))
        .then((users) => renderList(users, searchTerm))
        .catch((e) => {
          if (e.name !== 'AbortError') {
            renderList([], searchTerm);
          }
        });
    }, MENTION_SEARCH_DEBOUNCE);
  };
};
