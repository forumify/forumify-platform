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

    /**
     * @param Automation $automation
     * @param array<string, mixed>|null $payload
     * @return void
     */
    public function run(Automation $automation, ?array $payload): void
    {
        $args = $automation->getActionArguments();
        if (empty($args['endpoint'])) {
            return;
        }

        $endpoint = $args['endpoint'];
        $method = $args['method'] ?? 'get';
        $headers = $args['headers'] ?? null;
        if (empty($headers)) {
            $headers = [];
        } elseif (is_string($headers)) {
            $headers = $this->parseHeaders($headers);
        }

        $body = $args['body'] ?? null;
        if ($body !== null) {
            $body = $this->twig->createTemplate($body)->render($payload ?? []);
        }

        $this->httpClientFactory
            ->getClient()
            ->send(new Request($method, $endpoint, $headers, $body))
        ;
    }

    /**
     * @param string $headerData
     * @return array<string, string>
     */
    private function parseHeaders(string $headerData): array
    {
        $headerLines = explode("\n", $headerData);

        $headers = [];
        foreach ($headerLines as $headerLine) {
            $header = explode(':', $headerLine);
            if (count($header) !== 2) {
                continue;
            }
            $headers[trim($header[0])] = trim($header[1]);
        }
        return $headers;
    }
}
