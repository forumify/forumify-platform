<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class CardWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'layout.card';
    }

    public function getCategory(): string
    {
        return 'layout';
    }

    public function getPreview(): string
    {
        return '<div><div class="card">
            <div class="card-title widget-slot"></div>
            <div class="card-body widget-slot"></div>
            <div class="card-footer widget-slot"></div>
        </div></div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/card.html.twig';
    }
}
