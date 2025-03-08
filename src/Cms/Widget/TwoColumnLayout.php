<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class TwoColumnLayout extends AbstractWidget
{
    public function getName(): string
    {
        return 'layout.two_column';
    }

    public function getCategory(): string
    {
        return 'layout';
    }

    public function getPreview(): string
    {
        return '<div class="grid-2 h-100">
            <div class="col-1 pr-2 widget-slot border-r"></div>
            <div class="col-1 pl-2 widget-slot"></div>
        </div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/two_column.html.twig';
    }
}
