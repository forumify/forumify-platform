<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Repository\SettingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class RecaptchaService
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function verifyRequest(Request $request): float
    {
        trigger_deprecation('forumify/forumify-platform', '1.1', '%s has been replaced with %s', 'verifyRequest', 'isBot');
        return (float)$this->getScore($request) / 100;
    }

    public function isBot(Request $request): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $minScore = $this->settingRepository->get('forumify.recaptcha.min_score') ?? 0.8;
        if (!is_numeric($minScore) || $minScore < 0 || $minScore > 1) {
            $this->logger->error('forumify.recaptcha.min_score has an invalid value. Must be a number between 0 and 1.');
            return false;
        }

        $minScore = (int)($minScore * 100);
        $score = $this->getScore($request);
        return $score < $minScore;
    }

    private function getScore(Request $request): int
    {
        $token = $request->request->get('g-recaptcha-response');
        if (!$token) {
            return 0;
        }

        try {
            $result = $this
                ->httpClient
                ->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                    'body' => [
                        'secret' => $this->settingRepository->get('forumify.recaptcha.site_secret'),
                        'response' => $token,
                    ],
                ])
                ->toArray()
            ;
        } catch (Throwable $ex) {
            $result = ['success' => false];
        }

        $success = $result['success'] ?? false;
        if (!$success || empty($result['score'])) {
            $this->logger->warning('Unable to validate recaptcha score, access was allowed.', [
                'exception' => $ex ?? null,
                'result' => $result,
            ]);
            return 100;
        }

        return (int)($result['score'] * 100);
    }

    private function isEnabled(): bool
    {
        return $this->settingRepository->get('forumify.recaptcha.enabled')
            && $this->settingRepository->get('forumify.recaptcha.site_key')
            && $this->settingRepository->get('forumify.recaptcha.site_secret');
    }
}
