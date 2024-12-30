<?php

declare(strict_types=1);

namespace Forumify\Automation\Condition;

use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\ExpressionLangConditionType;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionLangCondition implements ConditionInterface
{
    public static function getType(): string
    {
        return 'Expression';
    }

    public function getPayloadFormType(): ?string
    {
        return ExpressionLangConditionType::class;
    }

    public function evaluate(Automation $automation, ?array $payload): bool
    {
        $expression = $automation->getConditionArguments()['expression'] ?? null;
        if (!$expression) {
            return false;
        }

        $expressionLanguage = new ExpressionLanguage();
        return (bool)$expressionLanguage->evaluate($expression, $payload ?? []);
    }
}
