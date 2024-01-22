<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Repository\SettingRepository;
use Twig\Extension\RuntimeExtensionInterface;

class SettingRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly SettingRepository $settingRepository)
    {
    }

    public function getSetting(string $key): int|string|float|array|null
    {
        return $this->settingRepository->get($key);
    }
}
