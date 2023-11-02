<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder;

class Menu
{
    private array $items = [];

    public function __construct(
        public readonly ?string $label = '',
        public readonly array $options = [],
        array $items = [],
    ) {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function addItem(Menu|MenuItem $item, ?int $position = 0): void
    {
        if (!isset($this->items[$position])) {
            $this->items[$position] = $item;
            return;
        }
        $this->addItem($item, $position + 1);
    }

    public function getEntries(): array
    {
        return array_values($this->items);
    }
}
