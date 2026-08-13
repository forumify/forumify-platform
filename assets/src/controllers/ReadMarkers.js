import { Controller } from '@hotwired/stimulus';

/**
 * Reading one marker can change the others on the page: reading a forum also reads its sub forums
 * and their topics. Rather than letting every marker refresh itself, this controller spans the whole
 * page and resolves them all in a single request.
 */
export class ReadMarkers extends Controller {
  static targets = ['marker'];

  static values = {
    url: String,
  };

  async markAsRead(event) {
    event.preventDefault();

    const marker = event.currentTarget;
    marker.classList.add('d-none');

    const response = await fetch(this.urlValue, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        subject: this.subject(marker),
        markers: this.markerTargets.map((target) => this.subject(target)),
      }),
    });

    if (!response.ok) {
      marker.classList.remove('d-none');
      return;
    }

    const { markers } = await response.json();
    const states = new Map(markers.map((state) => [this.key(state.type, state.id), state.read]));

    this.markerTargets.forEach((target) => {
      const read = states.get(this.key(target.dataset.readMarkerType, target.dataset.readMarkerId));
      if (read !== undefined) {
        target.classList.toggle('d-none', read);
      }
    });
  }

  subject(marker) {
    return {
      type: marker.dataset.readMarkerType,
      id: Number(marker.dataset.readMarkerId),
    };
  }

  // markers of different types can share an id, so the type is part of the key
  key(type, id) {
    return `${type}:${id}`;
  }
}
