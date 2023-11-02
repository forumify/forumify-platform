import { Controller } from '@hotwired/stimulus';
import { defaultValueCtx, Editor, rootCtx } from "@milkdown/core";
import { nord } from '@milkdown/theme-nord';
import { commonmark, toggleEmphasisCommand, toggleStrongCommand } from '@milkdown/preset-commonmark';
import { upload } from '@milkdown/plugin-upload';
import { gfm, toggleStrikethroughCommand } from '@milkdown/preset-gfm';
import { listener, listenerCtx } from '@milkdown/plugin-listener';
import { callCommand } from "@milkdown/utils";
import '@milkdown/theme-nord/style.css';

const TOOLBAR_COMMANDS = {
  toggleStrongCommand,
  toggleEmphasisCommand,
  toggleStrikethroughCommand,
};

// TODO: get rid of Milkdown, it's not a good editor. Let's do it the GitHub way.
export default class extends Controller {
  static targets = ['input', 'markdownEditor', 'sourceEditor'];

  initialize() {
    super.initialize();
    this.viewSource = false;
    this.editor = null;
  }

  connect() {
    Editor
      .make()
      .config(nord)
      .config((ctx) => {
        ctx.set(rootCtx, this.markdownEditorTarget);
      })
      .config((ctx) => {
        ctx.set(defaultValueCtx, this.inputTarget.value)
      })
      .config((ctx) => {
        ctx.get(listenerCtx).markdownUpdated((_, markdown) => {
          this.inputTarget.value = markdown;
        });
      })
      .use(commonmark)
      .use(gfm)
      .use(upload)
      .use(listener)
      .create()
      .then((editor) => this.editor = editor);
  }

  toolbarCommand(event) {
    const commandKey = event.target.dataset.command;
    const command = TOOLBAR_COMMANDS[commandKey];
    if (command !== undefined) {
      this.editor.action(callCommand(command.key));
    }
  }

  async toggleSource() {
    this.toggleInput(this.markdownEditorTarget);
    this.toggleInput(this.sourceEditorTarget);
    this.viewSource = !this.viewSource;

    if (!this.viewSource) {
      this.editor = await this.editor.create();
    }
  }

  toggleInput(input) {
    if (input.classList.contains('d-none')) {
      input.classList.remove('d-none');
    } else {
      input.classList.add('d-none');
    }
  }
}
