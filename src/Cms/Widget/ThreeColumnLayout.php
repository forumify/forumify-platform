<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class ThreeColumnLayout extends AbstractColumnLayout
{
    protected function getColumnCount(): int
    {
        return 3;
    }

    public function getName(): string
    {
        return 'layout.three_column';
    }
}
