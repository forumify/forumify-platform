<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Entity\Notification;

class GenericEmailNotificationType extends AbstractEmailNotificationType
{
    public const TYPE = 'generic_email';

    public function __construct(
        private readonly GenericNotificationType $genericNotificationType,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTitle(Notification $notification): string
    {
        return $this->genericNotificationType->getTitle($notification);
    }

    public function getDescription(Notification $notification): string
    {
        return $this->genericNotificationType->getDescription($notification);
    }

    public function getImage(Notification $notification): string
    {
        return $this->genericNotificationType->getImage($notification);
    }

    public function getUrl(Notification $notification): string
    {
        return $this->genericNotificationType->getUrl($notification);
    }

    public function getEmailTemplate(Notification $notification): string
    {
        return $this->getContext($notification)['emailTemplate'];
    }

    public function getEmailActionLabel(Notification $notification): string
    {
        return $this->getContext($notification)['emailActionLabel'];
    }

    /**
     * @return array{
     *     emailTemplate: string,
     *     emailActionLabel: string,
     * }
     */
    private function getContext(Notification $notification): array
    {
        $context = $notification->getDeserializedContext();
        return [
            'emailTemplate' => $context['emailTemplate'] ?? '@Forumify/emails/notifications/generic.html.twig',
            'emailActionLabel' => $context['emailActionLabel'] ?? '',
        ];
    }
}
