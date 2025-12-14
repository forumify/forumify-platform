import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import 'quill-mention/autoregister';

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
        ['link', 'image'],
        [{ color: [] }, { background: [] }],
        ['clean'],
      ],
      mention: {
        allowedChars: /^[A-Za-z\sÅÄÖåäö]*$/,
        mentionDenotationChars: ['@'],
        source: function (searchTerm, renderList) {
          if (searchTerm.length === 0) {
            renderList([]);
          }

          fetch('/users/search?query=' + searchTerm)
            .then((res) => res.json())
            .then((users) => users.map((user) => ({ id: user.id, value: user.displayName || user.username })))
            .then((users) => renderList(users));
        },
      },
    },
    theme: 'snow',
  });

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
