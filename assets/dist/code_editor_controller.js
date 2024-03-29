import { Controller } from '@hotwired/stimulus';
import * as ace from 'ace-builds';
import 'ace-builds/src-noconflict/mode-css';
import 'ace-builds/src-noconflict/mode-html';
import 'ace-builds/src-noconflict/mode-javascript';
import 'ace-builds/src-noconflict/mode-twig';
import 'ace-builds/src-noconflict/theme-github';
import 'ace-builds/src-noconflict/ext-language_tools';

export default class extends Controller {
  static values = {
    editorId: String,
    inputId: String,
    language: String,
    value: String,
  }

  connect() {
    const editor = ace.edit(this.editorIdValue, {
      theme: this.themeValue || 'ace/theme/github',
      value: this.valueValue || '',
      autoScrollEditorIntoView: true,
      maxLines: 40,
    });

    if (this.languageValue) {
      editor.setOption('mode', `ace/mode/${this.languageValue}`);
    }

    const inputElement = document.getElementById(this.inputIdValue);
    editor.on('change', () => {
      inputElement.value = editor.getSession().getValue();
    });
  }
}
