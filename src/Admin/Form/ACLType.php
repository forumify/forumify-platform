<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACL;
use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\ACLRepository;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ACLType extends AbstractType implements DataMapperInterface
{
    private AccessControlledEntityInterface $entity;

    public function __construct(
        private readonly ACLRepository $aclRepository,
        private readonly RoleRepository $roleRepository,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['roles', 'entity'])
            ->setAllowedTypes('roles', 'array')
            ->setAllowedTypes('entity', AccessControlledEntityInterface::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->entity = $options['entity'];
        foreach ($this->entity->getACLPermissions() as $permission) {
            /** @var Role $role */
            foreach ($options['roles'] as $role) {
                $builder->add("{$permission}__{$role->getId()}", CheckboxType::class, [
                    'required' => false,
                    'label' => false,
                ]);
            }
        }

        $builder->setDataMapper($this);
    }

    /**
     * @param array<ACL> $viewData
     * @param Traversable<string, FormInterface<mixed>> $forms
     * @return void
     */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        /** @var array<string, FormInterface<mixed>> $fields */
        $fields = iterator_to_array($forms);

        foreach ($viewData as $acl) {
            foreach ($acl->getRoles() as $role) {
                $key = "{$acl->getPermission()}__{$role->getId()}";
                if (!isset($fields[$key])) {
                    continue;
                }

                $fields[$key]->setData(true);
            }
        }
    }

    /**
     * @param Traversable<string, FormInterface<mixed>> $forms
     * @param-out array<ACL> $viewData
     * @return void
     */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        /** @var array<string, FormInterface<mixed>> $fields */
        $fields = iterator_to_array($forms);

        $viewData = [];
        foreach ($fields as $key => $value) {
            $roleHasPermission = $value->getData();
            [$permission, $roleId] = explode('__', $key);

            $acl = $this->findACL($viewData, $permission);
            if (!$roleHasPermission && $acl === null) {
                // no need to create ACL record if permission isn't enabled
                continue;
            }

            $role = $this->roleRepository->find($roleId);
            if ($role === null) {
                // in the off-chance the role was deleted between rendering the form and now
                continue;
            }

            if ($acl === null) {
                $aclParameters = $this->entity->getACLParameters();

                $acl = new ACL();
                $acl->setEntity($aclParameters->entity);
                $acl->setEntityId($aclParameters->entityId);
                $acl->setPermission($permission);
            }

            $aclRoles = $acl->getRoles();
            if ($roleHasPermission && !$aclRoles->contains($role)) {
                $aclRoles->add($role);
            }

            if (!$roleHasPermission && $aclRoles->contains($role)) {
                $aclRoles->removeElement($role);
            }

            $viewData[] = $acl;
        }
    }

    /**
     * @param array<ACL> $viewData
     */
    private function findACL(array $viewData, string $permission): ?ACL
    {
        foreach ($viewData as $acl) {
            if ($acl->getPermission() === $permission) {
                return $acl;
            }
        }

        return $this->aclRepository->findOneByEntityAndPermission($this->entity, $permission);
    }
}
