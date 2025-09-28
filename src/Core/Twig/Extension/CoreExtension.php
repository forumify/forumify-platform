<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Miscellaneous utility twig filters/functions/tests/...
 */
class CoreExtension extends AbstractExtension
{
    public function __construct()
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('short_number', $this->shortNumber(...)),
            new TwigFilter('format_date', [CoreRuntime::class, 'formatDate']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_demo', [CoreRuntime::class, 'isDemo']),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('instanceof', $this->instanceof(...)),
        ];
    }

    public function shortNumber(string|float|int $number): string
    {
        $number = is_string($number) ? (int)$number : $number;
        $number = (int)round($number);

        $divs = 0;
        while (strlen((string)$number) > 3) {
            $divs++;
            $number = intdiv($number, 1000);
        }

        // anything above sextillion would've probably made this crash already..
        $suffix = ['', 'K', 'M', 'B', 'T', 'aa', 'ab', 'ac'][$divs] ?? 'X';
        return $number . $suffix;
    }

    public function instanceof(mixed $object, string $classname): bool
    {
        if (!is_object($object)) {
            return false;
        }
        return $object instanceof $classname;
    }
}
