import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['image'];
  static values = {
    images: Array,
    autoscroll: Boolean,
    autoscrollInterval: Number,
  };

  connect() {
    this.index = 0;
    this.skipAutoscroll = false;
    this._updateImage();

    if (this.autoscrollValue) {
      const interval = Math.abs((this.autoscrollIntervalValue || 10) * 1000);
      this._autoscroll(interval);
    }
  }

  next() {
    this.skipAutoscroll = true;
    this._next();
  }

  previous() {
    this.skipAutoscroll = true;
    this.index--;
    this._updateImage();
  }

  _next() {
    this.index++;
    this._updateImage();
  }

  _updateImage() {
    const index = Math.abs(this.index % this.imagesValue.length);
    this.imageTarget.src = this.imagesValue[index];
  }

  _autoscroll(interval) {
    window.setTimeout(() => {
      this._autoscroll(interval);

      if (!this.skipAutoscroll) {
        this._next();
      }
      this.skipAutoscroll = false;
    }, interval);
  }
}
