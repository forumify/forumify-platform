<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder;

class Menu
{
    /**
     * @var array<Menu|MenuItem>
     */
    private array $items = [];
    private bool $sorted = false;

    /**
     * @param string|null $label
     * @param array<string> $options
     * @param array<MenuItem> $items
     */
    public function __construct(
        public readonly ?string $label = '',
        public readonly array $options = [],
        array $items = [],
    ) {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function addItem(Menu|MenuItem $item, ?int $position = 0): static
    {
        if (!isset($this->items[$position])) {
            $this->items[$position] = $item;
            return $this;
        }
        return $this->addItem($item, $position + 1);
    }

    /**
     * @return array<Menu|MenuItem>
     */
    public function getEntries(): array
    {
        if (!$this->sorted) {
            ksort($this->items);
            $this->sorted = true;
        }
        return $this->items;
    }

    public function removeItemAt(int $position): void
    {
        unset($this->items[$position]);
    }

    public function sortByLabel(): void
    {
        $labels = array_map(static fn (Menu|MenuItem $child) => ucfirst((string) $child->label), $this->items);
        array_multisort($labels, SORT_ASC, $this->items);
        $this->sorted = true;
    }

    public function getPermission(): ?string
    {
        return $this->options['permission'] ?? null;
    }
}
