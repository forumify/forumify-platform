import { Controller } from '@hotwired/stimulus';
import Chart from 'chart.js/auto';

export default class extends Controller {
  static values = {
    type: String,
    data: Array,
  }

  connect() {
    const style = getComputedStyle(document.body);
    const primary = style.getPropertyValue('--c-primary');

    const canvas = document.createElement('canvas');
    canvas.width = '100%';
    this.element.append(canvas);

    const chart = new Chart(canvas, {
      type: this.typeValue,
      data: {
        labels: this.dataValue.map((point) => point.label),
        datasets: [
          {
            data: this.dataValue.map((point) => point.value),
            borderColor: primary,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            display: false,
          },
        },
      },
    });

    window.addEventListener('resize', (e) => {
      chart.resize();
    });
  }
}
