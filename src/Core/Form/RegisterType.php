<?php

declare(strict_types=1);

namespace Forumify\Core\Form;

use Forumify\Core\Form\DTO\NewUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<NewUser>
 */
class RegisterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewUser::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => ['autocomplete' => 'username', 'autofocus' => 'autofocus'],
            ])
            ->add('email', EmailType::class, [
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password', 'attr' => ['autocomplete' => 'new-password']],
                'second_options' => ['label' => 'Repeat password', 'attr' => ['autocomplete' => 'new-password']],
            ])
            ->add('timezone', ChoiceType::class, [
                'autocomplete' => true,
                'placeholder' => 'Select a timezone',
                'choices' => $this->getTimezones(),
                'attr' => [
                    'data-controller' => 'forumify--timezone-input'
                ]
            ]);
    }

    private function getTimezones(): array
    {
        $timezones = \DateTimeZone::listIdentifiers();
        return array_combine($timezones, $timezones);
    }
}
