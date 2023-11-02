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

    public function getSetting(string $key): string
    {
        return $this->settingRepository->get($key);
    }
}
