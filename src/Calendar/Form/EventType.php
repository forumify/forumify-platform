<?php

declare(strict_types=1);

namespace Forumify\Calendar\Form;

use Forumify\Calendar\Entity\Calendar;
use Forumify\Calendar\Entity\CalendarEvent;
use Forumify\Calendar\Repository\CalendarRepository;
use Forumify\Core\Entity\User;
use Forumify\Core\Form\RichTextEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function __construct(
        private readonly CalendarRepository $calendarRepository,
        private readonly Security $security,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CalendarEvent::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        $builder
            ->add('calendar', EntityType::class, [
                'placeholder' => 'Select a calendar',
                'autocomplete' => true,
                'class' => Calendar::class,
                'choice_label' => 'title',
                'query_builder' => $this->calendarRepository->getManageableCalendarsQuery(),
            ])
            ->add('start', DateTimeType::class, [
                'widget' => 'single_text',
                'view_timezone' => $user?->getTimezone() ?? 'UTC',
            ])
            ->add('repeat', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'Never',
                'choices' => [
                    'Daily' => 'daily',
                    'Weekly' => 'weekly',
                    'Monthly' => 'monthly',
                    'Annually' => 'annually',
                ],
            ])
            ->add('repeatEnd', DateType::class, [
                'required' => false,
                'help' => 'When to stop repeating the event',
                'widget' => 'single_text',
            ])
            ->add('title', TextType::class)
            ->add('content', RichTextEditorType::class, [
                'required' => false,
                'empty_data' => '',
            ])
        ;
    }
}
