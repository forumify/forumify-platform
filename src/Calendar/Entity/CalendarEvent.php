<?php

declare(strict_types=1);

namespace Forumify\Calendar\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Calendar\Repository\CalendarEventRepository;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;

#[ORM\Entity(repositoryClass: CalendarEventRepository::class)]
class CalendarEvent
{
    use IdentifiableEntityTrait;
    use SluggableEntityTrait;
    use TimestampableEntityTrait;
    use BlameableEntityTrait;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'datetime')]
    private DateTime $start;

    #[ORM\Column('`repeat`', nullable: true)]
    private ?string $repeat = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $repeatEnd = null;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\ManyToOne(targetEntity: Calendar::class, inversedBy: 'events')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Calendar $calendar;

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function setStart(DateTime $start): void
    {
        $this->start = $start;
    }

    public function getRepeat(): ?string
    {
        return $this->repeat;
    }

    public function setRepeat(?string $repeat): void
    {
        $this->repeat = $repeat;
    }

    public function getRepeatEnd(): ?DateTime
    {
        return $this->repeatEnd;
    }

    public function setRepeatEnd(?DateTime $repeatEnd): void
    {
        $this->repeatEnd = $repeatEnd;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getCalendar(): Calendar
    {
        return $this->calendar;
    }

    public function setCalendar(Calendar $calendar): void
    {
        $this->calendar = $calendar;
    }
}
