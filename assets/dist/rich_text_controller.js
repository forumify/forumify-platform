import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  connect() {
    this.element.querySelectorAll('.mention').forEach((mention) => {
      mention.addEventListener('click', () => {
        location.href = `/profile/${mention.dataset.id}`;
      });

      mention.setAttribute('data-forumify--forumify-platform--profile-preview-user-id-value', mention.dataset.id);
      mention.dataset.controller = 'forumify--forumify-platform--profile-preview';
    });

    const children = [...this.element.children];
    children.forEach((child, i) => {
      if (child.tagName !== 'BLOCKQUOTE') {
        return;
      }

      const nextSibling = children[i + 1];
      if (nextSibling === undefined || nextSibling.tagName !== 'BLOCKQUOTE') {
        child.style.marginBottom = 'var(--spacing-2)';
      }
    });
  }
}
