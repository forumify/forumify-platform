<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Repository\SettingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class TurnstileService implements SpamProtectionServiceInterface
{
    private const string TURNSTILE_VERIFY_ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->settingRepository->get('forumify.cf_turnstile.enabled')
            && $this->settingRepository->get('forumify.cf_turnstile.site_key')
            && $this->settingRepository->get('forumify.cf_turnstile.site_secret');
    }

    public function isBot(): bool
    {
        $token = $this->requestStack->getCurrentRequest()?->request->get('cf-turnstile-response');
        if ($token === null) {
            return true;
        }

        try {
            $result = $this
                ->httpClient
                ->request('POST', self::TURNSTILE_VERIFY_ENDPOINT, [
                    'body' => [
                        'secret' => $this->settingRepository->get('forumify.cf_turnstile.site_secret'),
                        'response' => $token,
                    ],
                ])
                ->toArray();
        } catch (Throwable $ex) {
            $this->logger->warning('Unable to verify CF turnstile: ' . $ex->getMessage(), [
                'exception' => $ex,
            ]);
            $result = ['success' => false, 'error-codes' => ['internal-error']];
        }

        return !($result['success'] ?? false);
    }

    public function getJavascripts(string $formId): string
    {
        return <<<JS
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script type="text/javascript">
const cfSubmit = document.getElementById('$formId')?.querySelector('button[type="submit"]');
if (cfSubmit) {
    cfSubmit.style.opacity = 0;
}
function cfturnstileCallback() {
    cfSubmit.style.opacity = 1;
}
</script>
JS;
    }

    public function modifyButtonHtml(string $html): string
    {
        $theme = $this->requestStack->getCurrentRequest()?->cookies->get(ThemeService::CURRENT_THEME_COOKIE);
        $theme = match ($theme) {
            'default' => 'light',
            'dark' => 'dark',
            default => 'auto',
        };

        $siteKey = $this->settingRepository->get('forumify.cf_turnstile.site_key');

        return <<<HTML
<div class="cf-turnstile"
    data-sitekey="$siteKey"
    data-theme="$theme"
    data-size="flexible"
    data-callback="cfturnstileCallback"
    style="min-height: 65px;"
></div>
$html
HTML;
    }
}
