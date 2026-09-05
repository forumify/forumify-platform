<?php

declare(strict_types=1);

namespace Forumify\Core\Form\Extension;

use Forumify\Core\Service\UploadTransaction;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UploadTransactionExtension extends AbstractTypeExtension
{
    private const int AFTER_VALIDATION = -100;

    public function __construct(private readonly UploadTransaction $transaction)
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
                ? $this->transaction->writeUploads()
                : $this->transaction->discard();
        }, self::AFTER_VALIDATION);
    }
}
