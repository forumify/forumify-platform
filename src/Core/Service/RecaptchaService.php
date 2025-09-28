<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Repository\SettingRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class RecaptchaService
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function verifyRequest(Request $request): float
    {
        $token = $request->get('g-recaptcha-response');
        if (!$token) {
            return 0;
        }

        $params = [
            'body' => [
                'secret' => $this->settingRepository->get('forumify.recaptcha.site_secret'),
                'response' => $token
            ]
        ];

        try {
            $result = $this
                ->httpClient
                ->request('POST', 'https://www.google.com/recaptcha/api/siteverify', $params)
                ->toArray()
            ;
        } catch (Throwable) {
            return 0;
        }

        return $result['success'] ? $result['score'] : 0;
    }
}
