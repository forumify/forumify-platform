<?php

declare(strict_types=1);

namespace Forumify\Core\Security;

enum VoterAttribute: string
{
    case SuperAdmin = 'SUPER_ADMIN';
    case Administrator = 'ADMINISTRATOR';
    case Moderator = 'MODERATOR';

    case ACL = 'ACCESS_CONTROL_LIST';

    case ForumView = 'FORUM_VIEW';
    case ForumCreate = 'FORUM_CREATE';
    case ForumEdit = 'FORUM_EDIT';
    case ForumDelete = 'FORUM_DELETE';

    case TopicView = 'TOPIC_VIEW';
    case TopicCreate = 'TOPIC_CREATE';
    case TopicEdit = 'TOPIC_EDIT';
    case TopicDelete = 'TOPIC_DELETE';

    case CommentCreate = 'COMMENT_CREATE';
    case CommentEdit = 'COMMENT_EDIT';
    case CommentDelete = 'COMMENT_DELETE';

    case MessageThreadCreate = 'MESSAGE_THREAD_CREATE';
    case MessageThreadView = 'MESSAGE_THREAD_VIEW';
}
