import { Controller } from '@hotwired/stimulus';

export class RichText extends Controller {
  connect() {
    this.element.querySelectorAll('.mention').forEach((mention) => {
      mention.addEventListener('click', () => {
        location.href = `/profile/${mention.dataset.id}`;
      });

      mention.setAttribute('data-forumify--profile-preview-user-id-value', mention.dataset.id);
      mention.dataset.controller = 'forumify--profile-preview';
    });

    const isPlainBlockquote = (element) => element !== undefined
      && element.tagName === 'BLOCKQUOTE'
      && !element.classList.contains('forumify-quote');

    const children = [...this.element.children];
    children.forEach((child, i) => {
      if (isPlainBlockquote(child) && !isPlainBlockquote(children[i + 1])) {
        child.style.marginBottom = 'var(--spacing-2)';
      }
    });
  }
}
