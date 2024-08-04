<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Repository\SettingRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Request;

class RecaptchaService
{
    public function __construct(private readonly SettingRepository $settingRepository)
    {
    }

    public function verifyRequest(Request $request): float
    {
        $token = $request->get('g-recaptcha-response');
        if (!$token) {
            return 0;
        }

        try {
            $response = (new Client())
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'form_params' => [
                        'secret' => $this->settingRepository->get('forumify.recaptcha.site_secret'),
                        'response' => $token
                    ],
                ])
                ->getBody()
                ->getContents();

            $result = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            return $result['score'] ?? 0;
        } catch (GuzzleException|\JsonException) {
            return 0;
        }
    }
}
