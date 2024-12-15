<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Graph;

abstract class AbstractGraph
{
    public const TYPE_LINE = 'line';

    abstract public function getType(): string;

    /**
     * @return array<GraphDataPoint>
     */
    abstract public function getData(): array;
}
