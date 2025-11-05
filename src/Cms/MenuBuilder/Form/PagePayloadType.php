<?php

declare(strict_types=1);

namespace Forumify\Cms\MenuBuilder\Form;

use Forumify\Cms\Repository\PageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class PagePayloadType extends AbstractType
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $pages = $this->pageRepository->findAll();
        $pageChoices = [];
        foreach ($pages as $page) {
            $pageChoices[$page->getTitle()] = $page->getId();
        }

        $builder->add('page', ChoiceType::class, [
            'choices' => $pageChoices,
            'placeholder' => 'admin.menu_builder.page.select_page',
        ]);
    }
}
