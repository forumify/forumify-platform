<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class DoctrineTriggerType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entities = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $entityNames = array_map(static fn (ClassMetadata $metadata): string => $metadata->getName(), $entities);
        $entityChoices = array_combine($entityNames, $entityNames);
        ksort($entityChoices);

        $builder->add('entities', ChoiceType::class, [
            'autocomplete' => true,
            'multiple' => true,
            'label' => 'admin.automations.trigger.doctrine.entity',
            'placeholder' => 'admin.automations.trigger.doctrine.entity_placeholder',
            'help' =>'admin.automations.trigger.doctrine.entity_help',
            'required' => false,
            'choices' => $entityChoices,
        ]);
    }
}
