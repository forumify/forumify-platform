<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Forumify\Core\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class CoreRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function formatDate(DateTime|DateTimeImmutable|null $date): string
    {
        $t = $this->translator->trans(...);

        $past = ($date ?? new DateTime())->getTimestamp();
        $now = (new DateTime())->getTimestamp();

        $diff = abs($now - $past);
        $years = (int)floor($diff / (365 * 60 * 60 * 24));
        $months = (int)floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = (int)floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        $hours = (int)floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));

        if ($years === 0 && $months === 0 && $days === 0) {
            if ($hours !== 0) {
                return $t('date_relative.ago', ['relative' => $t('date_relative.hours', ['count' => $hours])]);
            }

            $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
            if ($minutes > 0) {
                return $t('date_relative.ago', ['relative' => $t('date_relative.minutes', ['count' => $minutes])]);
            }

            return $this->translator->trans('date_relative.now');
        }

        $user = $this->security->getUser();
        $timezone = $user instanceof User ? $user->getTimezone() : 'UTC';
        $date->setTimezone(new DateTimeZone($timezone));

        return $date->format('j M Y \a\t h:i A');
    }
}
