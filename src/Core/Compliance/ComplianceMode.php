<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance;

enum ComplianceMode: string
{
    case Off = 'off';
    case Generated = 'generated';
    case CmsPage = 'cms_page';
}
