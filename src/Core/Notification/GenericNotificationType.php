<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Forumify\Core\Entity\Notification;
use Symfony\Contracts\Translation\TranslatorInterface;

class GenericNotificationType implements NotificationTypeInterface
{
    public const TYPE = 'generic';

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTitle(Notification $notification): string
    {
        $context = $this->getContext($notification);
        return empty($context['title'])
            ? ''
            : $this->translator->trans($context['title'], $context['titleParams']);
    }

    public function getDescription(Notification $notification): string
    {
        $context = $this->getContext($notification);
        return empty($context['description'])
            ? ''
            : $this->translator->trans($context['description'], $context['descriptionParams']);
    }

    public function getImage(Notification $notification): string
    {
        return $this->getContext($notification)['image'];
    }

    public function getUrl(Notification $notification): string
    {
        return $this->getContext($notification)['url'];
    }

    public function handleNotification(Notification $notification): void
    {
        // no-op
    }

    /**
     * @return array{
     *     title: string,
     *     titleParams: array<string, string>,
     *     description: string,
     *     descriptionParams: array<string, string>,
     *     image: string,
     *     url: string,
     * }
     */
    private function getContext(Notification $notification): array
    {
        $context = $notification->getDeserializedContext();
        return [
            'title' => $context['title'] ?? '',
            'titleParams' => $context['titleParams'] ?? [],
            'description' => $context['description'] ?? '',
            'descriptionParams' => $context['descriptionParams'] ?? [],
            'image' => $context['image'] ?? '',
            'url' => $context['url'] ?? '',
        ];
    }
}
