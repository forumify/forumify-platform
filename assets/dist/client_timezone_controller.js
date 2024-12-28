import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  initialize() {
    this._autodetectTimezone = this._autodetectTimezone.bind(this);
  }

  connect() {
    this.element.addEventListener('autocomplete:pre-connect', this._autodetectTimezone);
  }

  disconnect() {
    this.element.removeEventListener('autocomplete:pre-connect', this._autodetectTimezone);
  }

  _autodetectTimezone(event) {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const option = this.element.querySelector(`[value="${timezone}"]`);
    if (option === null) {
      return;
    }

    option.selected = 'selected';
  }
}
