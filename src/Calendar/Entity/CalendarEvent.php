<?php

declare(strict_types=1);

namespace Forumify\Calendar\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Api\Serializer\Attribute\Asset;
use Forumify\Calendar\Repository\CalendarEventRepository;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource]
#[ORM\Entity(repositoryClass: CalendarEventRepository::class)]
class CalendarEvent
{
    use IdentifiableEntityTrait;
    use SluggableEntityTrait;
    use TimestampableEntityTrait;
    use BlameableEntityTrait;

    #[Groups('CalendarEvent')]
    #[ORM\Column(length: 255)]
    private string $title;

    #[Groups('CalendarEvent')]
    #[ORM\Column(type: 'datetime')]
    private DateTime $start;

    #[Groups('CalendarEvent')]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $end;

    #[Groups('CalendarEvent')]
    #[ORM\Column('`repeat`', nullable: true)]
    private ?string $repeat = null;

    #[Groups('CalendarEvent')]
    #[ORM\Column(nullable: true)]
    private ?DateTime $repeatEnd = null;

    #[Groups('CalendarEvent')]
    #[ORM\Column(type: 'text')]
    private string $content;

    #[Asset('forumify.asset')]
    #[Groups('CalendarEvent')]
    #[ORM\Column(nullable: true)]
    private ?string $banner = null;

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

    public function getEnd(): ?DateTime
    {
        return $this->end;
    }

    public function setEnd(?DateTime $end): void
    {
        $this->end = $end;
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

    public function getBanner(): ?string
    {
        return $this->banner;
    }

    public function setBanner(?string $banner): void
    {
        $this->banner = $banner;
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
