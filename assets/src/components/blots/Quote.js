import Quill from 'quill';

const BlockEmbed = Quill.import('blots/block/embed');

export class Quote extends BlockEmbed {
  static blotName = 'forumify-quote';
  static tagName = 'blockquote';
  static className = 'forumify-quote';

  static create(html) {
    const node = super.create();
    node.setAttribute('contenteditable', 'false');
    node.innerHTML = html;
    return node;
  }

  static value(node) {
    return node.innerHTML;
  }

  attach() {
    super.attach();

    if (this.domNode.getAttribute('contenteditable') !== 'false') {
      this.domNode.setAttribute('contenteditable', 'false');
    }
  }
}
