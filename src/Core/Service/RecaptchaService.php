<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Forumify\Core\Repository\SettingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class RecaptchaService implements SpamProtectionServiceInterface
{
    private const string GRECAPTCHA_VERIFY_ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->settingRepository->get('forumify.recaptcha.enabled')
            && $this->settingRepository->get('forumify.recaptcha.site_key')
            && $this->settingRepository->get('forumify.recaptcha.site_secret');
    }

    public function isBot(): bool
    {
        $token = $this->requestStack->getCurrentRequest()?->request->get('g-recaptcha-response');
        if (!$token) {
            return true;
        }

        $minScore = $this->settingRepository->get('forumify.recaptcha.min_score') ?? 0.8;
        if (!is_numeric($minScore) || $minScore < 0 || $minScore > 1) {
            $this->logger->error('forumify.recaptcha.min_score has an invalid value. Must be a number between 0 and 1.');
            return false;
        }
        $minScore = (int)($minScore * 100);

        try {
            $result = $this
                ->httpClient
                ->request('POST', self::GRECAPTCHA_VERIFY_ENDPOINT, [
                    'body' => [
                        'secret' => $this->settingRepository->get('forumify.recaptcha.site_secret'),
                        'response' => $token,
                    ],
                ])
                ->toArray();
        } catch (Throwable $ex) {
            $this->logger->warning('Unable to validate recaptcha score, access was allowed.', [
                'exception' => $ex,
            ]);
            return false;
        }

        $score = $result['score'] ?? 0;
        $score = (int)($score * 100);
        return $score < $minScore;
    }

    public function getJavascripts(string $formId): string
    {
        return <<<JS
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script type="text/javascript">function grecaptchaCallback() { document.getElementById('$formId').submit(); }</script>
JS;
    }

    public function modifyButtonHtml(string $html): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $buttons = new DOMXPath($dom)->query('//button[@type="submit"]');
        if ($buttons === false) {
            return $html;
        }

        $button = $buttons->item(0);
        if (!$button instanceof DOMElement) {
            return $html;
        }

        $button->setAttribute('class', $button->getAttribute('class') . ' g-recaptcha');
        $button->setAttribute('data-sitekey', $this->settingRepository->get('forumify.recaptcha.site_key'));
        $button->setAttribute('data-callback', 'grecaptchaCallback');

        return $dom->saveHTML() ?: $html;
    }
}
