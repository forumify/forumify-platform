import '../styles/frontend/index.scss';
import framework from './framework';
import { CommentEditor } from '../controllers/frontend/CommentEditor';
import { Messenger } from '../controllers/frontend/Messenger';

export default (app) => {
  const register = framework(app);

  register('comment-editor', CommentEditor);
  register('messenger', Messenger);
};
