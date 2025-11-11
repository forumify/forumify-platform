import { Controller } from '@hotwired/stimulus';
import { request } from '../../services/api';

export class Messenger extends Controller {
  static targets = [
    'thread',
    'titleInput',
    'titleEditBtn',
    'titleEditConfirmBtn',
  ];

  async confirmTitleEdit({ params }) {
    const threadId = params.thread;
    const title = this.titleInputTarget.value;

    const titleEl = this.threadTarget.querySelector('.thread-title');
    const oldTitle = titleEl.innerText;
    if (title === oldTitle) {
      this.toggleTitleEdit();
      return;
    }

    const res = await request(`/api/message-threads/${threadId}`, {
      method: 'PATCH',
      data: { title },
    });

    if (res.ok) {
      titleEl.innerText = title;
      this.element.querySelector(`a[data-live-thread-id-param="${threadId}"] .thread-title`).innerText = title;
      this.toggleTitleEdit();
    }
  }

  toggleTitleEdit() {
    this.threadTarget.querySelector('.thread-title').classList.toggle('d-none');
    this.threadTarget.querySelector('.thread-title-edit').classList.toggle('d-none');
    this.titleEditBtnTarget.classList.toggle('d-none');
    this.titleEditConfirmBtnTarget.classList.toggle('d-none');
  }
}
