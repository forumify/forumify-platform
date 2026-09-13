import { Controller } from '@hotwired/stimulus';
import { createPopper } from '@popperjs/core';

const HOVER_DELAY = 100;

const previewCache = new Map();

const fetchPreview = (userId) => {
  if (!previewCache.has(userId)) {
    previewCache.set(userId, fetch(`/profile/${userId}/preview`)
      .then((res) => res.text())
      .catch((error) => {
        previewCache.delete(userId);
        throw error;
      }));
  }

  return previewCache.get(userId);
};

export class ProfilePreview extends Controller {
  static values = {
    userId: Number,
  };

  connect() {
    this._popper = null;
    this._wrapper = null;
    this._hover = false;
    this._timeout = null;

    this._show = this._show.bind(this);
    this._hide = this._hide.bind(this);
    this.element.addEventListener('mouseenter', this._show);
    this.element.addEventListener('mouseleave', this._hide);
  }

  disconnect() {
    this.element.removeEventListener('mouseenter', this._show);
    this.element.removeEventListener('mouseleave', this._hide);

    clearTimeout(this._timeout);
    this._popper?.destroy();
    this._wrapper?.remove();
  }

  _show() {
    this._hover = true;
    if (this._wrapper !== null) {
      this._wrapper.classList.remove('d-none');
      this._popper.update();
      return;
    }

    clearTimeout(this._timeout);
    this._timeout = setTimeout(() => {
      fetchPreview(this.userIdValue).then(this._mountPreview.bind(this));
    }, HOVER_DELAY);
  }

  _mountPreview(html) {
    if (this._wrapper !== null) {
      return;
    }

    const wrapper = document.createElement('div');
    wrapper.classList.add('box', 'profile-preview');
    wrapper.innerHTML = html;
    document.body.append(wrapper);
    if (!this._hover) {
      // The user has already quit hovering but the fetch took too long
      wrapper.classList.add('d-none');
    }

    this._popper = createPopper(this.element, wrapper, {
      placement: 'bottom',
    });
    this._wrapper = wrapper;
  }

  _hide() {
    this._hover = false;
    clearTimeout(this._timeout);
    this._wrapper?.classList.add('d-none');
  }
}
