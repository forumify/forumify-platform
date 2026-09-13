<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\RichTextEditorType;
use Forumify\Core\Form\UserSelectType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<NewMessageThread>
 */
class NewMessageThreadType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => NewMessageThread::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('participants', UserSelectType::class, [
                'multiple' => true,
                'extra_options' => [
                    'exclude_users' => $this->getCurrentUserIds(),
                ],
            ])
            ->add('message', RichTextEditorType::class);
    }

    /**
     * @return list<int>
     */
    private function getCurrentUserIds(): array
    {
        $user = $this->security->getUser();
        return $user instanceof User ? [$user->getId()] : [];
    }
}
