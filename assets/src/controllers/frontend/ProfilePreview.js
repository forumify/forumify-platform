import { Controller } from '@hotwired/stimulus';
import { createPopper } from '@popperjs/core';

export class ProfilePreview extends Controller {
  static values = {
    userId: Number,
  };

  connect() {
    this._initialized = false;
    this._popper = null;
    this._wrapper = null;
    this._hover = false;

    this.element.addEventListener('mouseenter', this._dragstart.bind(this));
    this.element.addEventListener('mouseleave', this._dragend.bind(this));
  }

  _dragstart() {
    this._hover = true;
    if (this._initialized) {
      if (this._wrapper !== null) {
        this._wrapper.classList.remove('d-none');
        this._popper.update();
      }
      return;
    }

    this._initialized = true;

    fetch(`/profile/${this.userIdValue}/preview`)
      .then((res) => res.text())
      .then(this._mountPreview.bind(this));
  }

  _mountPreview(html) {
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

  _dragend() {
    this._hover = false;
    if (this._wrapper !== null) {
      this._wrapper.classList.add('d-none');
      this._popper.update();
    }
  }
}
