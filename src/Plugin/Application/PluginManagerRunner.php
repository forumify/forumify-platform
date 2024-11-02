<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Forumify\Plugin\Application\Service\PluginService;
use JsonException;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Runtime\RunnerInterface;

/**
 * Self-contained application runner that can work without Symfony container.
 * Plugins enable/disable Symfony bundles dynamically, doing so from within Symfony will cause critical errors.
 */
class PluginManagerRunner implements RunnerInterface
{
    private readonly Request $request;
    private readonly PluginService $pluginService;

    public function __construct(array $context)
    {
        $this->request = Request::createFromGlobals();
        $this->pluginService = new PluginService($context);
    }

    public function run(): int
    {
        try {
            $this->checkAuth();
        } catch (Exception $ex) {
            return $this->error('Unauthorized: ' . $ex->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        try {
            $body = json_decode($this->request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            return $this->error('Unable to decode body: ' . $ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $fn = $body['fn'] ?? null;
        if ($fn === null || !method_exists($this->pluginService, $fn)) {
            return $this->error('Function not set or missing.', Response::HTTP_BAD_REQUEST);
        }

        try {
            $args = $body['args'] ?? [];
            $output = $this->pluginService->$fn(...$args);
            return $this->success($output);
        } catch (Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function checkAuth(): void
    {
        $token = $this->request->headers->get('Authorization');
        if ($token === null) {
            throw new RuntimeException('No authorization methods found.');
        }
        $decodedToken = (array)JWT::decode($token, new Key($_ENV['APP_SECRET'], 'HS256'));
        if (empty($decodedToken['resource_access']) || !in_array('plugin-manager', $decodedToken['resource_access'], true)) {
            throw new RuntimeException('No access to plugin manager.');
        }
    }

    private function error(string $msg, int $status = Response::HTTP_INTERNAL_SERVER_ERROR): int
    {
        (new JsonResponse(['success' => false, 'error' => $msg], $status))->prepare($this->request)->send();
        return 0;
    }

    private function success(string $output): int
    {
        (new JsonResponse(['success' => true, 'output' => $output]))->prepare($this->request)->send();
        return 0;
    }
}
