<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance\Disclosure;

enum CookieCategory: string
{
    case Necessary = 'necessary';
    case Functional = 'functional';
    case Analytics = 'analytics';
    case Marketing = 'marketing';
}
