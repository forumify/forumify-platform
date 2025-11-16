<?php

declare(strict_types=1);

namespace Forumify\Cms\Form;

use Forumify\Cms\Entity\Page;
use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
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
        /** @var Page|null $page */
        $page = $options['data'] ?? null;

        $builder
            ->add('title', TextType::class)
            ->add('urlKey', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
        ;

        if ($page === null) {
            $builder->add('type', ChoiceType::class, [
                'help' => 'admin.cms.pages.type_help',
                'placeholder' => 'admin.cms.pages.type_select',
                'choices' => [
                    'Twig' => Page::TYPE_TWIG,
                    'Page Builder' => Page::TYPE_BUILDER,
                ]
            ]);
            return;
        }

        $builder
            ->add('seoDescription', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('seoKeywords', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('javascript', CodeEditorType::class, [
                'label' => false,
                'language' => CodeEditorLanguage::JavaScript->value,
                'required' => false,
                'empty_data' => '',
                'density' => 'fullscreen',
            ])
            ->add('css', CodeEditorType::class, [
                'label' => false,
                'language' => CodeEditorLanguage::Css->value,
                'required' => false,
                'empty_data' => '',
                'density' => 'fullscreen',
            ])
        ;

        switch ($page->getType()) {
            case Page::TYPE_TWIG:
                $builder->add('twig', CodeEditorType::class, [
                    'label' => false,
                    'language' => CodeEditorLanguage::Twig->value,
                    'required' => false,
                    'empty_data' => '',
                    'density' => 'fullscreen',
                ]);
                break;
            case Page::TYPE_BUILDER:
                $builder->add('twig', HiddenType::class);
                break;
            default:
                break;
        }
    }
}
