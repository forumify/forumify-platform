<?php

declare(strict_types=1);

namespace Forumify\Cms\Form;

use Forumify\Cms\Entity\Snippet;
use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Forumify\Core\Form\RichTextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SnippetType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Snippet::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Snippet|null $snippet */
        $snippet = $options['data'] ?? null;

        $builder->add('name', TextType::class, [
            'disabled' => $snippet !== null,
        ]);

        if ($snippet !== null) {
            $builder->add('slug', TextType::class, [
                'disabled' => true,
            ]);
        }

        $builder->add('type', ChoiceType::class, [
            'choices' => [
                'Html (Twig)' => 'html',
                'Rich Text' => 'rich_text',
            ],
            'disabled' => $snippet !== null,
        ]);

        if ($snippet === null) {
            return;
        }

        $type = [
            'html' => [
                'type' => CodeEditorType::class,
                'options' => [
                    'language' => CodeEditorLanguage::Twig->value,
                ],
            ],
            'rich_text' => [
                'type' => RichTextEditorType::class,
                'options' => [],
            ],
        ][$snippet->getType()];

        $builder->add('content', $type['type'], $type['options']);
    }
}
