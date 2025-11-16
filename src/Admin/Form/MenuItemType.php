<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Doctrine\ORM\EntityRepository;
use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\MenuTypeInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\String\u;

/**
 * @extends AbstractType<MenuItem>
 */
class MenuItemType extends AbstractType
{
    /**
     * @var array<MenuTypeInterface>
     */
    private array $menuTypes = [];

    /**
     * @param iterable<MenuTypeInterface> $menuTypes
     */
    public function __construct(
        #[AutowireIterator('forumify.menu_builder.type')]
        iterable $menuTypes,
    ) {
        /** @var MenuTypeInterface $menuType */
        foreach ($menuTypes as $menuType) {
            $this->menuTypes[$menuType->getType()] = $menuType;
        }
        ksort($this->menuTypes);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuItem::class,
            'parent' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var MenuItem|null $menuItem */
        $menuItem = $options['data'] ?? null;
        $menuType = $menuItem !== null
            ? $this->menuTypes[$menuItem->getType()]
            : null;

        $builder
            ->add('name')
            ->add('type', ChoiceType::class, [
                'choices' => array_keys($this->menuTypes),
                'choice_label' => $this->typeLabel(...),
                'placeholder' => 'admin.menu_builder.select_type',
                'disabled' => $menuItem !== null,
            ])
            ->add('parent', EntityType::class, [
                'class' => MenuItem::class,
                'required' => false,
                'placeholder' => 'admin.menu_builder.parent_root',
                'choice_label' => 'name',
                'help' => 'admin.menu_builder.parent_help',
                'data' => $menuItem?->getParent() ?? $options['parent'] ?? null,
                'query_builder' => fn (EntityRepository $repository) => $repository
                    ->createQueryBuilder('mi')
                    ->where('mi.type = :type')
                    ->setParameter('type', 'collection')
                    ->orderBy('mi.position', 'ASC'),
            ]);

        $payloadType = $menuType?->getPayloadFormType();
        if ($payloadType !== null) {
            $builder->add('payload', $payloadType, [
                'label' => false,
            ]);
        }
    }

    private function typeLabel(string $type): string
    {
        return u($type)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->title()
            ->toString();
    }
}
