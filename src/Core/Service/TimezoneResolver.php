<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use DateTimeZone;
use Exception;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\SettingRepository;
use Symfony\Bundle\SecurityBundle\Security;

class TimezoneResolver
{
    public const string DEFAULT_TIMEZONE_SETTING = 'forumify.default_timezone';

    public function __construct(
        private readonly Security $security,
        private readonly SettingRepository $settingRepository,
    ) {
    }

    public function getTimezone(): DateTimeZone
    {
        $user = $this->security->getUser();
        $userTimezone = $user instanceof User
            ? $user->getTimezone()
            : null;

        $defaultTimezone = $this->settingRepository->get(self::DEFAULT_TIMEZONE_SETTING);

        return $this->createTimezone($userTimezone)
            ?? $this->createTimezone(is_string($defaultTimezone) ? $defaultTimezone : null)
            ?? new DateTimeZone('UTC');
    }

    private function createTimezone(?string $timezone): ?DateTimeZone
    {
        if (empty($timezone)) {
            return null;
        }

        try {
            return new DateTimeZone($timezone);
        } catch (Exception) {
            return null;
        }
    }
}
