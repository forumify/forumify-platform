<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Service;

use Forumify\Core\Service\SpamProtectionService;
use Forumify\Core\Service\SpamProtectionServiceInterface;
use PHPUnit\Framework\TestCase;

class SpamProtectionServiceTest extends TestCase
{
    public function testIsEnabled(): void
    {
        $enabledService = $this->createMock(SpamProtectionServiceInterface::class);
        $enabledService->method('isEnabled')->willReturn(true);

        $enabled = new SpamProtectionService([$enabledService])->isEnabled();

        self::assertTrue($enabled);
    }

    public function testIsEnabledNoServices(): void
    {
        $disabledService = $this->createMock(SpamProtectionServiceInterface::class);
        $disabledService->method('isEnabled')->willReturn(false);

        $enabled = new SpamProtectionService([$disabledService])->isEnabled();

        self::assertFalse($enabled);
    }

    public function testIsBot(): void
    {
        $botService = $this->createMock(SpamProtectionServiceInterface::class);
        $botService->method('isEnabled')->willReturn(true);
        $botService->method('isBot')->willReturn(true);

        $isBot = new SpamProtectionService([$botService])->isBot();

        self::assertTrue($isBot);
    }

    public function testIsBotHuman(): void
    {
        $humanService = $this->createMock(SpamProtectionServiceInterface::class);
        $humanService->method('isEnabled')->willReturn(true);
        $humanService->method('isBot')->willReturn(false);

        $isBot = new SpamProtectionService([$humanService])->isBot();

        self::assertFalse($isBot);
    }

    public function testIsBotIgnoresDisabledServices(): void
    {
        $disabledService = $this->createMock(SpamProtectionServiceInterface::class);
        $disabledService->method('isEnabled')->willReturn(false);
        $disabledService->method('isBot')->willReturn(true);

        $enabledService = $this->createMock(SpamProtectionServiceInterface::class);
        $enabledService->method('isEnabled')->willReturn(true);
        $enabledService->method('isBot')->willReturn(false);

        $service = new SpamProtectionService([$disabledService, $enabledService]);

        self::assertFalse($service->isBot());
    }

    public function testGetJavascriptsReturnsJavascriptsFromAllEnabledServices(): void
    {
        $service1 = $this->createMock(SpamProtectionServiceInterface::class);
        $service1->method('isEnabled')->willReturn(true);
        $service1->method('getJavascripts')->with('formId')->willReturn('<script>service1</script>');

        $service2 = $this->createMock(SpamProtectionServiceInterface::class);
        $service2->method('isEnabled')->willReturn(true);
        $service2->method('getJavascripts')->with('formId')->willReturn('<script>service2</script>');

        $service = new SpamProtectionService([$service1, $service2]);

        $javascripts = $service->getJavascripts('formId');

        self::assertStringContainsString('service1', $javascripts);
        self::assertStringContainsString('service2', $javascripts);
    }

    public function testGetJavascriptsIgnoresDisabledServices(): void
    {
        $disabledService = $this->createMock(SpamProtectionServiceInterface::class);
        $disabledService->method('isEnabled')->willReturn(false);
        $disabledService->method('getJavascripts')->willReturn('<script>disabled</script>');

        $enabledService = $this->createMock(SpamProtectionServiceInterface::class);
        $enabledService->method('isEnabled')->willReturn(true);
        $enabledService->method('getJavascripts')->with('formId')->willReturn('<script>enabled</script>');

        $service = new SpamProtectionService([$disabledService, $enabledService]);

        $javascripts = $service->getJavascripts('formId');

        self::assertStringNotContainsString('disabled', $javascripts);
        self::assertStringContainsString('enabled', $javascripts);
    }

    public function testModifyButtonHtmlModifiesHtmlFromAllEnabledServices(): void
    {
        $service1 = $this->createMock(SpamProtectionServiceInterface::class);
        $service1->method('isEnabled')->willReturn(true);
        $service1->method('modifyButtonHtml')->with('<button>Submit</button>')->willReturn('<div>Service1</div><button>Submit</button>');

        $service2 = $this->createMock(SpamProtectionServiceInterface::class);
        $service2->method('isEnabled')->willReturn(true);
        $service2->method('modifyButtonHtml')->with('<div>Service1</div><button>Submit</button>')->willReturn('<div>Service2</div><div>Service1</div><button>Submit</button>');

        $service = new SpamProtectionService([$service1, $service2]);

        $html = $service->modifyButtonHtml('<button>Submit</button>');

        self::assertStringContainsString('Service1', $html);
        self::assertStringContainsString('Service2', $html);
        self::assertStringContainsString('Submit', $html);
    }

    public function testModifyButtonHtmlIgnoresDisabledServices(): void
    {
        $disabledService = $this->createMock(SpamProtectionServiceInterface::class);
        $disabledService->method('isEnabled')->willReturn(false);
        $disabledService->method('modifyButtonHtml')->willReturn('<div>disabled</div><button>Submit</button>');

        $enabledService = $this->createMock(SpamProtectionServiceInterface::class);
        $enabledService->method('isEnabled')->willReturn(true);
        $enabledService->method('modifyButtonHtml')->with('<button>Submit</button>')->willReturn('<div>enabled</div><button>Submit</button>');

        $service = new SpamProtectionService([$disabledService, $enabledService]);

        $html = $service->modifyButtonHtml('<button>Submit</button>');

        self::assertStringNotContainsString('disabled', $html);
        self::assertStringContainsString('enabled', $html);
    }
}
