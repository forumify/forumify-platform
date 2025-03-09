<?php

declare(strict_types=1);

namespace Forumify\Cms\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResponsiveColumnSizeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('columns');
        $resolver->setRequired('columns');
        $resolver->setAllowedTypes('columns', 'int');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $choices = array_combine($choices, $choices);

        for ($i = 0; $i < $options['columns']; $i++) {
            $builder->add('column' . ($i + 1), ChoiceType::class, [
                'placeholder' => 12 / $options['columns'],
                'choices' => $choices,
            ]);
        }
    }
}
