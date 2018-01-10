<?php

namespace Bayard\RollingLog\EventSubscriber;

use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Bayard\RollingLog\Exception\BayardRollingLogException;
use Bayard\RollingLog\Serializer\DoctrineEntitySerializer;

class LogDoctrineEventSubscriber extends AbstractLogSubscriber implements EventSubscriber
{

    use DoctrineEntitySerializer;

    protected $eventsMessages = [
        Events::preRemove => "Preparing to Remove",
        Events::postRemove => "Removed",
        Events::postPersist => "Created",
        Events::postUpdate => "Updated"
    ];

    public function getSubscribedEvents()
    {
        return array_keys($this->eventsMessages);
    }

    protected function getLifecycleEventArgs(LifecycleEventArgs $args)
    {
        return $args;
    }

    // public function setSerializer(ArrayTransformerInterface $serializer)
    // {
    //     $this->serializer = (null === $serializer) ?
    //         $serializer = new DoctrineEntitySerializer() :
    //         $serializer;
    // }

    public function __call($name, $args)
    {
        if (!array_key_exists($name, $this->eventsMessages)) {
            throw new BayardRollingLogException("Calling an inexisting Method or unsuscribed Event: ".$name);
        }

        $eargs = $this->getLifecycleEventArgs($args[0]);
        $entity = $eargs->getObject();
        $entityName = $this->getSimpleClassName(get_class($entity));
        $entityManager = $eargs->getObjectManager();

        try {
            $entityId = $entity->getId();
        } catch (Exception $e) {
            $entityId = null;
        }

        $context = call_user_func_array([$this, 'getContextFor'.ucfirst($name)], [$entityManager, $entity]);

        $message = (null === $entityId)?
            $this->eventsMessages[$name] . " " . $entityName :
            $this->eventsMessages[$name] . " " . $entityName . " with ID: " . $entityId;

        $this->logEvent($message, $context);
    }

    public function getContextForPreRemove(EntityManager $entityManager, $entity)
    {
        $original_data = $entityManager->getUnitOfWork()->getOriginalEntityData($entity);
        $removing_values = [];

        foreach ($original_data as $attr => $value) {
            if (is_object($value)) {
                $removing_values[$attr] = $this->objectAsName($value);
            } else {
                $removing_values[$attr] = $value;
            }
        }

        return $removing_values;
    }

    public function getContextForPostRemove(EntityManager $entityManager, $entity)
    {
        $context = $this->serializeObject($entity);

        return $context;

    }

    public function getContextForPostPersist(EntityManager $entityManager, $entity)
    {
        return $this->toArray($entityManager, $entity);
    }

    public function getContextForPostUpdate(EntityManager $entityManager, $entity)
    {
        $change_set = $entityManager->getUnitOfWork()->getEntityChangeSet($entity);

        foreach ($change_set as $attr => $values) {
            foreach ($values as $i => $value) {
                if (is_object($value)) {
                    $change_set[$attr][$i] = $this->objectAsName($value);
                }
            }
        }

        return $change_set;
    }



}