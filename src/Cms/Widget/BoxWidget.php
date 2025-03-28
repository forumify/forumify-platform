<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class BoxWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'layout.box';
    }

    public function getCategory(): string
    {
        return 'layout';
    }

    public function getPreview(): string
    {
        return '<div class="box">
            <div class="widget-slot"></div>
        </div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/box.html.twig';
    }
}
