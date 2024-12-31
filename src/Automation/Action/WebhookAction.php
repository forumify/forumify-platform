<?php

declare(strict_types=1);

namespace Forumify\Automation\Action;

use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\WebhookActionType;
use Forumify\Core\Service\HttpClientFactory;
use GuzzleHttp\Psr7\Request;
use Twig\Environment;

class WebhookAction implements ActionInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly HttpClientFactory $httpClientFactory,
    ) {
    }

    public static function getType(): string
    {
        return 'Webhook';
    }

    public function getPayloadFormType(): ?string
    {
        return WebhookActionType::class;
    }

    public function run(Automation $automation, ?array $payload): void
    {
        $args = $automation->getActionArguments();
        if (empty($args['webhookUrl']) || empty($args['data'])) {
            return;
        }

        $endpoint = $args['webhookUrl'];
        $headers = ['Content-Type' => 'application/json'];
        $body = $this->twig->createTemplate($args['data'])->render($payload ?? []);

        $this->httpClientFactory
            ->getClient()
            ->send(new Request('post', $endpoint, $headers, $body))
        ;
    }
}
