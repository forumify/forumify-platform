import { Controller } from '@hotwired/stimulus';
import { request } from '../services/api';

export class ReadMarkers extends Controller {
  static targets = ['marker'];

  static values = {
    url: String,
  };

  async markAsRead(event) {
    event.preventDefault();

    const marker = event.currentTarget;
    marker.classList.add('d-none');

    let states;
    try {
      const response = await request(this.urlValue, {
        method: 'POST',
        data: {
          subject: this.subject(marker),
          markers: this.markerTargets.map((target) => this.subject(target)),
        },
      });

      if (!response.ok) {
        throw new Error(`Marking as read failed with status ${response.status}.`);
      }

      const { markers } = await response.json();
      states = new Map(markers.map((state) => [this.key(state.type, state.id), state.read]));
    } catch (error) {
      marker.classList.remove('d-none');
      throw error;
    }

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

  key(type, id) {
    return `${type}:${id}`;
  }
}
