import '../styles/frontend/index.scss';
import framework from './framework';
import { CommentEditor } from '../controllers/frontend/CommentEditor';
import { DiscordWidget } from '../controllers/frontend/DiscordWidget';
import { Messenger } from '../controllers/frontend/Messenger';
import { TeamspeakWidget } from '../controllers/frontend/TeamspeakWidget';

export default (app) => {
  const register = framework(app);

  register('comment-editor', CommentEditor);
  register('discord-widget', DiscordWidget);
  register('messenger', Messenger);
  register('teamspeak-widget', TeamspeakWidget);
};
