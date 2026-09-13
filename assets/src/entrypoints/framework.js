import '@phosphor-icons/web/regular';
import '@phosphor-icons/web/fill';
import '@symfony/ux-live-component';
import { Chart } from '../controllers/Chart';
import { CodeEditor } from '../controllers/CodeEditor';
import { FileUpload } from '../controllers/FileUpload';
import { Gallery } from '../controllers/Gallery';
import { List } from '../controllers/List';
import { Menu } from '../controllers/Menu';
import { Modal } from '../controllers/Modal';
import { ProfilePreview } from '../controllers/ProfilePreview';
import { ReadMarkers } from '../controllers/ReadMarkers';
import { RichText } from '../controllers/RichText';
import { RichTextEditor } from '../controllers/RichTextEditor';
import { Tabs } from '../controllers/Tabs';
import { Theme } from '../controllers/Theme';
import { TimezoneInput } from '../controllers/TimezoneInput';
import { Upload } from '../controllers/Upload';
import { Youtube } from '../controllers/Youtube';

export default (app) => {
  const register = (name, controller) => {
    app.register(`forumify--${name}`, controller);
  };

  register('chart', Chart);
  register('code-editor', CodeEditor);
  register('file-upload', FileUpload);
  register('gallery', Gallery);
  register('list', List);
  register('menu', Menu);
  register('modal', Modal);
  register('profile-preview', ProfilePreview);
  register('read-markers', ReadMarkers);
  register('rich-text', RichText);
  register('rich-text-editor', RichTextEditor);
  register('tabs', Tabs);
  register('theme', Theme);
  register('timezone-input', TimezoneInput);
  register('upload', Upload);
  register('youtube', Youtube);

  return register;
};
