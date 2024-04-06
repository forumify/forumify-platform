<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\MenuTypeInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemType extends AbstractType
{
    /**
     * @var array<MenuTypeInterface>
     */
    private array $menuTypes;

    public function __construct(
        #[TaggedIterator('forumify.menu_builder.type')]
        iterable $menuTypes,
    ) {
        /** @var MenuTypeInterface $menuType */
        foreach ($menuTypes as $menuType) {
            $this->menuTypes[$menuType->getType()] = $menuType;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuItem::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var MenuItem|null $menuItem */
        $menuItem = $options['data'] ?? null;
        $menuType = $menuItem !== null
            ? $this->menuTypes[$menuItem->getType()]
            : null;

        $typeOptions = array_keys($this->menuTypes);

        $builder
            ->add('name')
            ->add('type', ChoiceType::class, [
                'choices' => array_combine($typeOptions, $typeOptions),
                'placeholder' => 'admin.menu_builder.select_type',
                'disabled' => $menuItem !== null,
            ]);

        $payloadType = $menuType?->getPayloadFormType();
        if ($payloadType !== null) {
            $builder->add('payload', $payloadType, [
                'label' => false,
            ]);
        }
    }
}
