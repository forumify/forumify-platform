<?php
declare(strict_types=1);

namespace Tests\Tests\Traits;

use Forumify\Core\Repository\SettingRepository;

trait SettingTrait
{
    use RequiresContainerTrait;

    private function setSetting(string $key, mixed $value): void
    {
        self::getContainer()->get(SettingRepository::class)->set($key, $value);
    }
}
