import '../styles/admin/index.scss'
import framework from './framework';
import { Admin } from '../controllers/admin/Admin';
import { PageBuilder } from '../controllers/admin/PageBuilder';
import { PluginManager } from '../controllers/admin/PluginManager';
import { TemplateEditor } from '../controllers/admin/TemplateEditor';

const register = framework();

register('admin', Admin);
register('page-builder', PageBuilder);
register('plugin-manager', PluginManager);
register('template-editor', TemplateEditor);
