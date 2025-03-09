<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class FourColumnLayout extends AbstractColumnLayout
{
    protected function getColumnCount(): int
    {
        return 4;
    }

    public function getName(): string
    {
        return 'layout.four_column';
    }
}
