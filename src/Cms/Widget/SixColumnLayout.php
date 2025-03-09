<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class SixColumnLayout extends AbstractColumnLayout
{
    protected function getColumnCount(): int
    {
        return 6;
    }

    public function getName(): string
    {
        return 'layout.six_column';
    }
}
