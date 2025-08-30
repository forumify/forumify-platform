import '../styles/frontend/index.scss'
import framework from './framework';
import { CommentEditor } from '../controllers/frontend/CommentEditor';
import { ProfilePreview } from '../controllers/frontend/ProfilePreview';
import { Messenger } from '../controllers/frontend/Messenger';

const register = framework();

register('comment-editor', CommentEditor);
register('profile-preview', ProfilePreview);
register('messenger', Messenger);
