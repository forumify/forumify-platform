<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Admin\Form\ACLType;
use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACLParameters;
use Forumify\Core\Repository\ACLRepository;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ACLController extends AbstractController
{
    #[Route('/acl', name: 'acl')]
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        RoleRepository $roleRepository,
        ACLRepository $aclRepository,
    ): Response {
        $aclParameters = ACLParameters::fromRequest($request);

        $entityClass = $aclParameters->entity;
        /** @var class-string<object> $entityClass */
        $entityId = $aclParameters->entityId;
        $entity = $entityManager->getRepository($entityClass)->find($entityId);

        if ($entity === null) {
            throw $this->createNotFoundException(sprintf(
                'Unable to find %s with id %s',
                $entityClass,
                $entityId
            ));
        }

        if (!$entity instanceof AccessControlledEntityInterface) {
            throw new \InvalidArgumentException(sprintf(
                '%s is not controlled by ACL, please implement %s',
                get_class($entity),
                AccessControlledEntityInterface::class,
            ));
        }

        $permissions = $entity->getACLPermissions();
        $roles = $roleRepository->findAll();
        $form = $this->createForm(
            ACLType::class,
            $aclRepository->findByEntity($entity),
            [
                'roles' => $roles,
                'entity' => $entity,
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $aclRepository->saveAll($data);

            $this->addFlash('success', 'flashes.acl_saved');
            return $this->redirectToRoute($aclParameters->returnPath, $aclParameters->returnParameters);
        }

        return $this->render('@Forumify/admin/acl/acl.html.twig', [
            'form' => $form->createView(),
            'permissions' => $permissions,
            'roles' => $roles,
            'aclParameters' => $aclParameters,
        ]);
    }
}
