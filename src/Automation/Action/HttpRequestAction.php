<?php

declare(strict_types=1);

namespace Forumify\Automation\Action;

use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\HttpRequestActionType;
use Forumify\Core\Service\HttpClientFactory;
use GuzzleHttp\Psr7\Request;
use Twig\Environment;

class HttpRequestAction implements ActionInterface
{
    public function __construct(
        private readonly HttpClientFactory $httpClientFactory,
        private readonly Environment $twig,
    ) {
    }

    public static function getType(): string
    {
        return 'HTTP Request';
    }

    public function getPayloadFormType(): ?string
    {
        return HttpRequestActionType::class;
    }

    public function run(Automation $automation, ?array $payload): void
    {
        $args = $automation->getActionArguments();
        if (empty($args['endpoint'])) {
            return;
        }

        $endpoint = $args['endpoint'];
        $method = $args['method'] ?? 'get';
        $body = $args['body'] ?? null;
        if ($body !== null) {
            $body = $this->parseBody($body, $payload ?? []);
        }

        $request = new Request($method, $endpoint, [
            'Content-Type' => 'application/json',
        ], $body);

        $client = $this->httpClientFactory->getClient();
        $client->send($request);
    }

    private function parseBody(string $body, array $payload): string
    {
        return $this->twig
            ->createTemplate($body)
            ->render($payload)
        ;
    }
}
