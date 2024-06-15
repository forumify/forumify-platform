import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['errorBox', 'pluginList', 'loader'];

  loading = false;

  send(event) {
    if (this.loading) {
      return;
    }

    this.errorBoxTarget.classList.add('d-none');
    this.setLoading(true);

    const requestInfo = {
      method: 'POST',
      body: JSON.stringify(event.params),
    };

    fetch('/plugins', requestInfo)
      .then((res) => res.json())
      .then(this.handleResponse.bind(this))
      .catch((error) => {
        console.error(error);
        this.handleResponse({});
      });
  }

  install(event) {
    event.preventDefault();
    event.target.querySelector('button[type="submit"]').disabled = true;

    const pkg = event.target.querySelector('#plugin-package').value;
    this.send({
      params: {
        action: 'install',
        package: pkg,
      }
    });
  }

  handleResponse(data) {
    if (data.success) {
      window.location.href = '/admin/plugins/refresh';
      return;
    }

    this.setLoading(false);
    this.errorBoxTarget.innerText = data.error || 'An unknown error occurred';
    this.errorBoxTarget.classList.remove('d-none');
  }

  setLoading(loading) {
    this.loading = loading;
    if (loading) {
      this.loaderTarget.classList.remove('d-none');
      this.pluginListTarget.classList.add('d-none');
    } else {
      this.loaderTarget.classList.add('d-none');
      this.pluginListTarget.classList.remove('d-none');
    }
  }
}
