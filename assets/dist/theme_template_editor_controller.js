import { Controller } from '@hotwired/stimulus';
import * as ace from 'ace-builds';
import 'ace-builds/src-noconflict/mode-twig';
import 'ace-builds/src-noconflict/theme-github';
import 'ace-builds/src-noconflict/ext-language_tools';

export default class extends Controller {
  openFiles = {};

  connect() {
    this.element.__component.on('render:finished', () => {
      const newEditors = this._findNewEditorIds();
      newEditors.forEach(this._createTemplateEditors.bind(this));

      if (newEditors.length > 0) {
        this._selectTab(newEditors[0]);
      }
    });

    document.addEventListener('theme-template-editor:reset-editor', (e) => {
      const { fileId, location } = e.detail;

    });
  }

  toggleDir(e) {
    const button = e.target.parentElement.nodeName === 'A'
      ? e.target.parentElement
      : e.target;

    const children = button.parentElement.querySelector('.directory-children');
    if (children === null) {
      return;
    }

    button.querySelector('.ph').classList.toggle('ph-caret-right');
    button.querySelector('.ph').classList.toggle('ph-caret-down');

    children.classList.toggle('closed');
  }

  selectTab(e) {
    this._selectTab(e.params.fileId);
  }

  selectLocation(e) {
    this._selectLocation(e.params.fileId, e.params.location)
  }

  closeTab(e) {
    const { fileId } = e.params;
    if (this.openFiles[fileId]) {
      this.openFiles[fileId].forEach((aceEditor) => aceEditor.destroy());
      delete this.openFiles[fileId];
    }
    const openFileIds = Object.keys(this.openFiles);
    if (openFileIds.length > 0) {
      this._selectTab(openFileIds[openFileIds.length - 1]);
    }
  }

  _selectTab(id) {
    this.element.querySelectorAll('.template-group').forEach((el) => el.classList.add('d-none'));
    this.element.querySelector(`#${id}`).classList.remove('d-none');

    this.element.querySelectorAll('.template-tab').forEach((el) => el.style.borderBottom = 'none');
    this.element.querySelector(`#template-tab-${id}`).style.borderBottom = 'solid 2px var(--c-primary)';
  }

  _selectLocation(fileId, location) {
    const container = this.element.querySelector(`#${fileId}`);

    container.querySelectorAll('.template-editor').forEach((el) => el.classList.add('d-none'));
    container.querySelector(`#editor-${location}-${fileId}`).classList.remove('d-none');

    container.querySelectorAll('.location-tab').forEach((el) => el.style.borderBottom = 'none');
    container.querySelector(`#tab-${location}-${fileId}`).style.borderBottom = 'solid 2px var(--c-primary)';
  }

  _findNewEditorIds() {
    const openFileIds = [...this.element.querySelectorAll('.template-group')].map((el) => el.id);
    const knownFileIds = Object.keys(this.openFiles);

    return openFileIds.filter((id) => !knownFileIds.includes(id));
  }

  _createTemplateEditors(fileId) {
    const fileElement = document.getElementById(fileId);
    if (!fileElement) {
      return;
    }

    this.openFiles[fileId] = [];
    fileElement.querySelectorAll('.template-editor').forEach((editorWrapper) => {
      if (editorWrapper.dataset.initialValue === undefined) {
        return;
      }

      const editor = ace.edit(editorWrapper, {
        theme: 'ace/theme/github',
        mode: 'ace/mode/twig',
        value: editorWrapper.dataset.initialValue,
        readOnly: editorWrapper.dataset.location !== 'local',
      });

      this.openFiles[fileId].push(editor);
      this._selectLocation(fileId, editorWrapper.dataset.location);
    });
  }
}
