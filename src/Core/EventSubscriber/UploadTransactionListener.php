<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Forumify\Core\Service\UploadTransaction;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(KernelEvents::RESPONSE)]
class UploadTransactionListener
{
    public function __construct(private readonly UploadTransaction $transaction)
    {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $event->getResponse()->getStatusCode() < Response::HTTP_BAD_REQUEST
            ? $this->transaction->commit()
            : $this->transaction->rollback();
    }
}
