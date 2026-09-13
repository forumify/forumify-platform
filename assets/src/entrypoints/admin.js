import '../styles/admin/index.scss';
import framework from './framework';
import { Admin } from '../controllers/admin/Admin';
import { Compliance } from '../controllers/admin/Compliance';
import { PageBuilder } from '../controllers/admin/PageBuilder';
import { PluginManager } from '../controllers/admin/PluginManager';
import { TemplateEditor } from '../controllers/admin/TemplateEditor';

export default (app) => {
  const register = framework(app);

  register('admin', Admin);
  register('compliance', Compliance);
  register('page-builder', PageBuilder);
  register('plugin-manager', PluginManager);
  register('template-editor', TemplateEditor);
};
