<?php

declare(strict_types=1);

namespace Forumify\Page\Form;

use Forumify\Page\Entity\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('urlKey', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'HTML' => 'html',
                ]
            ]);

        if (($options['data'] ?? null) !== null) {
            $builder->add('source', TextareaType::class);
        }
    }
}
