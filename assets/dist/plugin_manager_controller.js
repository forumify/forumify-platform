import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = { token: String }
  static targets = ['log', 'pluginList', 'progress'];

  commandNames = {
    setActive: 'Toggling active state',
    composerRequire: 'Installing plugin package',
    composerUpdate: 'Updating PHP dependencies',
    composerRemove: 'Removing plugin package',
    composerPostInstall: 'Executing post install scripts',
    clearFrameworkCache: 'Clearing framework cache',
    migrations: 'Running database migrations',
    npmUpdate: 'Updating frontend dependencies',
    npmBuild: 'Building frontend assets'
  };
  inProgress = false;
  progress = 0;
  maxProgress = 0;

  updateAll() {
    this.run([
      ['composerUpdate'],
      ['clearFrameworkCache'],
      ['composerPostInstall'],
      ['migrations'],
      ['npmUpdate'],
      ['npmBuild'],
    ]);
  }

  updatePlatform({ params }) {
    const version = '^' + params.split('.').slice(0, 2).join('.');
    this.updatePackage({ params: { package: 'forumify/forumify-platform', version } });
  }

  updatePackage({ params }) {
    const args = {};
    params.package !== undefined && (args.package = params.package);
    params.version !== undefined && (args.version = params.version);

    this.run([
      ['composerUpdate', args],
      ['clearFrameworkCache'],
      ['composerPostInstall'],
      ['migrations'],
      ['npmUpdate'],
      ['npmBuild'],
    ]);
  }

  install(event) {
    event.preventDefault();

    const packageInput = event.target.querySelector('#plugin-package');
    this.run([
      ['composerRequire', { package: packageInput.value }],
      ['clearFrameworkCache'],
      ['composerPostInstall'],
      ['npmUpdate'],
      ['npmBuild'],
    ]);
  }

  uninstall({ params }) {
    this.run([
      ['composerRemove', { package: params.package }],
      ['clearFrameworkCache'],
      ['composerPostInstall'],
      ['npmUpdate'],
      ['npmBuild'],
    ]);
  }

  activate({ params }) {
    this.run([
      ['setActive', { pluginId: params.plugin, active: true }],
      ['clearFrameworkCache'],
      ['composerPostInstall'],
      ['migrations'],
    ])
  }

  deactivate({ params }) {
    this.run([
      ['setActive', { pluginId: params.plugin, active: false }],
      ['clearFrameworkCache'],
      ['composerPostInstall'],
    ]);
  }

  async run(commands) {
    if (this.inProgress) {
      return;
    }
    this.start(commands.length);

    let success = true;
    for (const command of commands) {
      const fn = command[0];
      const args = command[1] || {};

      this.log(`Running step ${fn} with args ${JSON.stringify(args)}`);
      this.step(fn);

      try {
        const response = await this.send(fn, args);
        const body = await response.json();
        if (!body.success) {
          throw new Error(body.error || 'Unknown error occurred.');
        }
        if (body.output) {
          this.log(body.output);
        }
      } catch (e) {
        this.error(e);
        success = false;
        break;
      }
    }

    if (success) {
      window.location.href = '/admin/plugins/refresh';
      return;
    }
    this.end();
  }

  send(fn, args) {
    return fetch('/plugins', {
      method: 'POST',
      body: JSON.stringify({ fn, args }),
      headers: { Authorization: this.tokenValue },
    });
  }

  start(length) {
    this.inProgress = true;
    this.maxProgress = length;
    this.progress = 0;

    this.progressTarget.classList.remove('d-none');
    this.progressTarget.querySelector('.progress-bar').style.width = '0%';

    this.logTarget.classList.remove('d-none');
    this.logTarget.querySelector('pre').innerText = 'Debug log:';

    this.pluginListTarget.classList.add('d-none');
  }

  step(command) {
    this.progress++;

    const stepWidth = 100 / this.maxProgress;
    const width = stepWidth * this.progress;
    this.progressTarget.querySelector('.progress-bar').style.width = width + '%';

    const stepNameLabel = this.progressTarget.querySelector('.step');
    stepNameLabel.innerText = this.commandNames[command] || command;
  }

  end() {
    this.pluginListTarget.classList.remove('d-none');
    this.progressTarget.classList.add('d-none');
    this.logTarget.querySelector('.alert-error').classList.remove('d-none');
    this.inProgress = false;
  }

  log(msg) {
    const logEl = this.logTarget.querySelector('pre');
    logEl.textContent += `\n${msg}`;
  }

  error(msg) {
    this.log(`[!] ${msg}`);
  }
}
