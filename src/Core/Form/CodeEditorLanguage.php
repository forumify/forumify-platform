<?php
declare(strict_types=1);

namespace Forumify\Core\Form;

enum CodeEditorLanguage: string
{
    case Css = 'css';
    case Html = 'html';
    case JavaScript = 'javascript';
    case Twig = 'twig';
}
