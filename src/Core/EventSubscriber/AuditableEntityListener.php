<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Forumify\Core\Entity\AuditableEntityInterface;
use Forumify\Core\Entity\AuditExcludedField;
use Forumify\Core\Entity\AuditLog;
use Forumify\Core\Entity\SensitiveField;
use Forumify\Core\Entity\User;
use Forumify\Core\Notification\ContextSerializer;
use Forumify\OAuth\Entity\OAuthClient;
use JsonSerializable;
use ReflectionAttribute;
use ReflectionClass;
use Stringable;
use Symfony\Bundle\SecurityBundle\Security;
use Throwable;

#[AsDoctrineListener(event: Events::postPersist, priority: -999)]
#[AsDoctrineListener(event: Events::preUpdate, priority: -999)]
#[AsDoctrineListener(event: Events::postUpdate, priority: -999)]
#[AsDoctrineListener(event: Events::preRemove, priority: -999)]
#[AsDoctrineListener(event: Events::postRemove, priority: -999)]
class AuditableEntityListener
{
    /** @var array<int, array<mixed>> */
    private array $changesets = [];
    /** @var array<int, string> */
    private array $removals = [];
    /** @var array<string, array<string, array<string, bool>>> */
    private array $markedFields = [];

    public function __construct(
        private readonly ContextSerializer $contextSerializer,
        private readonly EntityManagerInterface $em,
        private readonly AuditableWriteListener $writeListener,
        private readonly Security $security,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof AuditableEntityInterface) {
            return;
        }

        $log = new AuditLog();
        $log->user = $this->getUser();
        $log->action = 'create';
        $log->targetEntityClass = get_class($entity);
        $log->targetEntityId = $entity->getIdentifierForAudit();
        $log->targetName = $entity->getNameForAudit();

        $this->writeListener->queueLog($log);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof AuditableEntityInterface) {
            return;
        }

        $this->changesets[spl_object_id($entity)] = $this->getChangeset($args);
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof AuditableEntityInterface) {
            return;
        }

        $changeset = $this->changesets[spl_object_id($entity)] ?? null;
        if (empty($changeset)) {
            return;
        }

        $log = new AuditLog();
        $log->user = $this->getUser();
        $log->action = 'update';
        $log->targetEntityClass = get_class($entity);
        $log->targetEntityId = $entity->getIdentifierForAudit();
        $log->targetName = $entity->getNameForAudit();
        $log->changeset = $changeset;

        $this->writeListener->queueLog($log);
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof AuditableEntityInterface) {
            return;
        }

        $this->removals[spl_object_id($entity)] = $entity->getIdentifierForAudit();
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof AuditableEntityInterface) {
            return;
        }

        $log = new AuditLog();
        $log->user = $this->getUser();
        $log->action = 'remove';
        $log->targetEntityClass = get_class($entity);
        $log->targetEntityId = $this->removals[spl_object_id($entity)] ?? null;
        $log->targetName = $entity->getNameForAudit();

        $this->writeListener->queueLog($log);
    }

    private function getUser(): ?User
    {
        $user = $this->security->getUser();
        if ($user instanceof User) {
            return $user;
        }

        if ($user instanceof OAuthClient) {
            return $user->getUser();
        }

        return null;
    }

    /**
     * @param array<string, array{0: mixed, 1: mixed}>
     * @return array<string, array{0: string, 1: string}>
     */
    private function getChangeset(PreUpdateEventArgs $args): array
    {
        $entity = $args->getObject();
        $excludedFields = $this->getMarkedFields($entity, AuditExcludedField::class);
        $sensitiveFields = $this->getMarkedFields($entity, SensitiveField::class);

        $changeset = [];
        foreach ($args->getEntityChangeSet() as $k => $changes) {
            if (!is_array($changes) || count($changes) !== 2) {
                continue;
            }

            if (isset($excludedFields[$k])) {
                continue;
            }

            if (isset($sensitiveFields[$k])) {
                $changeset[$k] = ['********', '********'];
                continue;
            }

            $changeset[$k] = [
                $this->serializeValue($changes[0]),
                $this->serializeValue($changes[1]),
            ];
        }

        return $changeset;
    }

    private function serializeValue(mixed $v): string
    {
        if (is_bool($v)) {
            $v = $v ? 'Yes' : 'No';
        } elseif (is_scalar($v) || $v instanceof Stringable) {
            $v = (string)$v;
        } elseif (is_object($v)) {
            $cls = get_class($v);
            try {
                $metadata = $this->em->getClassMetadata($cls);
                $ids = $metadata->getIdentifierValues($v);
                $v = $cls . '(' . implode(',', $ids) . ')';
            } catch (Throwable) {
                if ($v instanceof JsonSerializable) {
                    $v = json_encode($v);
                } else {
                    $v = $cls;
                }
            }
        } elseif ($v === null) {
            $v = 'NULL';
        } else {
            $v = '???';
        }

        $v = (string)$v;
        if (strlen($v) > 1024) {
            $v = substr($v, 0, 1024) . '... (truncated)';
        }

        return $v;
    }

    /**
     * @return array<string>
     */
    private function getMarkedFields(object $entity, string $attribute): array
    {
        $class = get_class($entity);
        if (isset($this->markedFields[$attribute][$class])) {
            return $this->markedFields[$attribute][$class];
        }

        $fields = [];
        $refl = new ReflectionClass($class);
        foreach ($refl->getProperties() as $property) {
            $sensitiveArgs = $property->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF);
            if (!empty($sensitiveArgs)) {
                $fields[$property->name] = true;
            }
        }

        return $this->markedFields[$attribute][$class] = $fields;
    }
}
