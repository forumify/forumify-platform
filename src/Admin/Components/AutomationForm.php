<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Forumify\Automation\Action\ActionInterface;
use Forumify\Automation\AutomationComponentInterface;
use Forumify\Automation\Condition\ConditionInterface;
use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Repository\AutomationRepository;
use Forumify\Automation\Trigger\TriggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[IsGranted('forumify.admin.automations.manage')]
#[AsLiveComponent('Forumify\\AutomationForm', '@Forumify/admin/components/automation/form.html.twig')]
class AutomationForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    /**
     * @param TriggerInterface[] $triggers
     * @param ConditionInterface[] $conditions
     * @param ActionInterface[] $actions
     */
    public function __construct(
        #[AutowireIterator('forumify.automation.trigger', defaultIndexMethod: 'getType')]
        private readonly iterable $triggers,
        #[AutowireIterator('forumify.automation.condition', defaultIndexMethod: 'getType')]
        private readonly iterable $conditions,
        #[AutowireIterator('forumify.automation.action', defaultIndexMethod: 'getType')]
        private readonly iterable $actions,
        private readonly AutomationRepository $automationRepository,
    ) {
    }

    #[LiveProp]
    public ?Automation $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        $builder = $this->createFormBuilder($this->initialFormData, ['data_class' => Automation::class]);
        $builder
            ->add('name', TextType::class)
            ->add('enabled', CheckboxType::class, [
                'required' => false,
            ])
        ;
        $this->addAutomationComponentFields('trigger', $builder, true);
        $this->addAutomationComponentFields('condition', $builder, false);
        $this->addAutomationComponentFields('action', $builder, true);

        return $builder->getForm();
    }

    #[LiveAction]
    public function save(): Response
    {
        $this->submitForm();

        /** @var Automation $automation */
        $automation = $this->getForm()->getData();
        $this->automationRepository->save($automation);

        $this->addFlash('success', 'admin.automations.saved');
        return $this->redirectToRoute('forumify_admin_automation_form', ['id' => $automation->getId()]);
    }

    private function addAutomationComponentFields(string $component, FormBuilderInterface $builder, bool $required): void
    {
        $thisIterable = "{$component}s";

        /** @var array<string, AutomationComponentInterface> $components */
        $components = iterator_to_array($this->$thisIterable);
        $choices = array_combine(array_keys($components), array_keys($components));
        ksort($choices);

        $builder->add($component, ChoiceType::class, [
            'autocomplete' => true,
            'placeholder' => "admin.automations.{$component}.placeholder",
            'required' => $required,
            'choices' => $choices,
        ]);

        $getter = 'get' . ucfirst($component);
        $selectedComponent = empty($this->formValues[$component])
            ? $this->initialFormData?->$getter()
            : $this->formValues[$component];

        $formType = $selectedComponent !== null && isset($components[$selectedComponent])
            ? $components[$selectedComponent]->getPayloadFormType()
            : null;

        if ($formType) {
            $builder->add("{$component}Arguments", $formType, [
                'label' => false,
            ]);
        }
    }
}
