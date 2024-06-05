<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Twig\Extension;

use Forumify\Core\Twig\Extension\CoreExtension;
use PHPUnit\Framework\TestCase;

class CoreExtensionTest extends TestCase
{
    /** @dataProvider shortNumberProvider */
    public function testShortNumber(mixed $input, string $expected): void
    {
        $extension = new CoreExtension();
        $actual = $extension->shortNumber($input);
        self::assertSame($expected, $actual);
    }

    public function shortNumberProvider(): iterable
    {
        yield 'hundred' => [100, '100'];
        yield 'thousands' => [100_000, '100K'];
        yield 'millions' => [100_000_000, '100M'];
        yield 'billions' => [100_000_000_000, '100B'];
        yield 'trillions' => [100_000_000_000_000, '100T'];

        yield 'string' => ['12345', '12K'];
        yield 'float' => [12345.67, '12K'];
    }
}
