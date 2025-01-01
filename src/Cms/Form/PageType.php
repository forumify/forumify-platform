<?php

declare(strict_types=1);

namespace Forumify\Cms\Form;

use Forumify\Cms\Entity\Page;
use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Symfony\Component\Form\AbstractType;
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
        /** @var Page|null $page */
        $page = $options['data'] ?? null;

        $builder
            ->add('title', TextType::class)
            ->add('urlKey', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('seoDescription', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('seoKeywords', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('twig', CodeEditorType::class, [
                'label' => false,
                'language' => CodeEditorLanguage::Twig->value,
                'required' => false,
                'data' => $page === null
                    ? $this->defaultTwigTemplate()
                    : $page->getTwig(),
                'empty_data' => '',
            ])
            ->add('css', CodeEditorType::class, [
                'label' => false,
                'language' => CodeEditorLanguage::Css->value,
                'required' => false,
                'empty_data' => '',
            ])
            ->add('javascript', CodeEditorType::class, [
                'label' => false,
                'language' => CodeEditorLanguage::JavaScript->value,
                'required' => false,
                'empty_data' => '',
            ]);
    }

    private function defaultTwigTemplate(): string
    {
        return <<<DOC
{% extends '@Forumify/frontend/page.html.twig' %}
{% block body %}
<p>
    Learn more about customizing pages in the <a href="https://docs.forumify.net/user-manual/cms" target="_blank">CMS documentation.</a>
</p>
{% endblock %}
DOC;
    }
}
