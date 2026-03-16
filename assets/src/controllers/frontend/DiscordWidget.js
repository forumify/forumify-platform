import { Controller } from '@hotwired/stimulus';

export class DiscordWidget extends Controller {
  static values = {
    serverId: String,
  };
  static targets = ['iframe'];

  openModal() {
    if (this.iframeTarget.getAttribute('src')) {
      return;
    }

    const frameUrl = `https://discord.com/widget?id=${this.serverIdValue}&theme=${theme}`;
    this.iframeTarget.setAttribute('src', frameUrl);
  }
}
