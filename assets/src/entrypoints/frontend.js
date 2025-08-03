import '../styles/frontend/index.scss'
import framework from './framework';
import { CommentEditor } from '../controllers/frontend/CommentEditor';
import { ProfilePreview } from '../controllers/frontend/ProfilePreview';


const register = framework();

register('comment-editor', CommentEditor);
register('profile-preview', ProfilePreview);
