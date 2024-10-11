import Quill from 'quill';
import 'quill/dist/quill.snow.css'

/**
 * @param {HTMLElement} element
 * @returns {Quill}
 * @constructor
 */
export default function RichTextEditor(element) {
  const quill = new Quill(element, {
    modules: {
      toolbar: [
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        ['bold', 'italic', 'underline'],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'align': [] }],
        ['blockquote', 'code-block'],
        ['link', 'image'],
        [{ 'color': [] }, { 'background': [] }],
        ['clean']
      ],
    },
    theme: 'snow',
  });

  quill.clipboard.addMatcher(Node.ELEMENT_NODE, (node, delta) => {
    delta.forEach((d) => {
      if(d.attributes){
        d.attributes.color = '';
        d.attributes.background = '';
      }
    });
    return delta;
  });

  return quill;
}
