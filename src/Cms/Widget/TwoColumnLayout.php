<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class TwoColumnLayout extends AbstractColumnLayout
{
    protected function getColumnCount(): int
    {
        return 2;
    }

    public function getName(): string
    {
        return 'layout.two_column';
    }
}
