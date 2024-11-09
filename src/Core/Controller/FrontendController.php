<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Core\Attribute\AsFrontend;
use Forumify\Core\Event\FrontendEvents;
use Forumify\Core\Event\FrontendRenderEvent;
use Forumify\Core\Event\FrontendRequestEvent;
use Forumify\Core\Event\FrontendResponseEvent;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\Core\Security\VoterAttribute;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FrontendController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $frontend = $request->attributes->get('_frontend');
        if ($frontend === null) {
            throw $this->createNotFoundException();
        }

        $requestEvent = new FrontendRequestEvent($frontend, $request);
        $this->eventDispatcher->dispatch(
            $requestEvent,
            FrontendEvents::getName(FrontendEvents::REQUEST, $frontend)
        );
        $request = $requestEvent->request;

        $reflection = new \ReflectionClass($frontend);
        $attributes = $reflection->getAttributes(AsFrontend::class);

        /** @var AsFrontend|null $attribute */
        $attribute = ($attributes[0] ?? null)->newInstance();
        if ($attribute === null) {
            throw $this->createNotFoundException();
        }

        $repository = $this->entityManager->getRepository($frontend);
        if (!$repository instanceof AbstractRepository) {
            throw new RuntimeException("$frontend must have a repository that extends " . AbstractRepository::class);
        }

        $item = null;
        $idKey = $attribute->identifier;
        if ($id = $request->get($idKey)) {
            $item = $repository->findOneBy([$idKey => $id]);
        }

        $permission = $attribute->permission;
        if ($item !== null && $permission !== null) {
            $this->denyAccessUnlessGranted(VoterAttribute::ACL->value, [
                'permission' => $permission,
                'entity' => $item,
            ]);
        }

        $renderEvent = new FrontendRenderEvent($frontend, $item, $repository, [
            'repository' => $repository,
            'item' => $item,
        ]);
        $this->eventDispatcher->dispatch(
            $renderEvent,
            FrontendEvents::getName(FrontendEvents::RENDER, $frontend)
        );
        $response = $this->render($attribute->template, $renderEvent->templateParameters);

        $responseEvent = new FrontendResponseEvent($frontend, $response);
        $this->eventDispatcher->dispatch(
            $responseEvent,
            FrontendEvents::getName(FrontendEvents::RESPONSE, $frontend),
        );
        return $responseEvent->response;
    }
}
