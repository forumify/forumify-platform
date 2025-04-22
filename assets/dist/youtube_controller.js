import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    videoId: String,
  };

  connect() {
    const thumbnail = new Image();
    thumbnail.src = `https://img.youtube.com/vi/${this.videoIdValue}/sddefault.jpg`;
    thumbnail.addEventListener('load', () => {
      this.element.appendChild(thumbnail);
    });
    this.element.addEventListener('click', this._playVideo.bind(this));
  }

  _playVideo() {
    const iframe = document.createElement('iframe');
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allowfullscreen', '');
    iframe.setAttribute('src', `https://www.youtube.com/embed/${this.videoIdValue}?rel=0&showinfo=0&autoplay=1`);

    this.element.innerHTML = '';
    this.element.appendChild(iframe);
  }
}
