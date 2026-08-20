<?php

declare(strict_types=1);

namespace Forumify\Core\Form\Extension;

use Forumify\Core\Service\FileDeletionQueue;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FileDeletionExtension extends AbstractTypeExtension
{
    private const int AFTER_VALIDATION = -100;

    public function __construct(private readonly FileDeletionQueue $deletionQueue)
    {
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();
            if (!$form->isRoot()) {
                return;
            }

            $form->isValid()
                ? $this->deletionQueue->commit()
                : $this->deletionQueue->discard();
        }, self::AFTER_VALIDATION);
    }
}
