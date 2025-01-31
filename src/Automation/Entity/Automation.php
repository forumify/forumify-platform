<?php

declare(strict_types=1);

namespace Forumify\Automation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Automation\Repository\AutomationRepository;
use Forumify\Core\Entity\IdentifiableEntityTrait;

#[ORM\Entity(repositoryClass: AutomationRepository::class)]
#[ORM\Index(name: 'trigger_idx', fields: ['trigger'])]
class Automation
{
    use IdentifiableEntityTrait;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\Column('`trigger`')]
    private string $trigger;

    #[ORM\Column(type: 'json', nullable: true)]
    private mixed $triggerArguments = [];

    #[ORM\Column('`condition`', nullable: true)]
    private ?string $condition;

    #[ORM\Column(type: 'json', nullable: true)]
    private mixed $conditionArguments = [];

    #[ORM\Column]
    private string $action;

    #[ORM\Column(type: 'json', nullable: true)]
    private mixed $actionArguments = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): void
    {
        $this->trigger = $trigger;
    }

    public function getTriggerArguments(): mixed
    {
        return $this->triggerArguments;
    }

    public function setTriggerArguments(mixed $triggerArguments): void
    {
        $this->triggerArguments = $triggerArguments;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): void
    {
        $this->condition = $condition;
    }

    public function getConditionArguments(): mixed
    {
        return $this->conditionArguments;
    }

    public function setConditionArguments(mixed $conditionArguments): void
    {
        $this->conditionArguments = $conditionArguments;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getActionArguments(): mixed
    {
        return $this->actionArguments;
    }

    public function setActionArguments(mixed $actionArguments): void
    {
        $this->actionArguments = $actionArguments;
    }
}
