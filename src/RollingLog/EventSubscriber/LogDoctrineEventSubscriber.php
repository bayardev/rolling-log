<?php

namespace Bayard\RollingLog\EventSubscriber;

use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Bayard\RollingLog\Exception\BayardRollingLogException;
use Bayard\RollingLog\Serializer\DoctrineEntitySerializerInterface;
use Bayard\RollingLog\Serializer\DoctrineEntitySerializer;

class LogDoctrineEventSubscriber extends AbstractLogSubscriber implements EventSubscriber
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    protected $eventsMessages = [
        Events::preRemove => "Preparing to Remove",
        Events::postRemove => "Removed",
        Events::postPersist => "Created",
        Events::postUpdate => "Updated"
    ];

    function __construct(LoggerInterface $logger)
    {
        parent::__construct();
        $this->setSerializer();
    }

    public function getSubscribedEvents()
    {
        return array_keys($this->eventsMessages);
    }

    protected function getLifecycleEventArgs(LifecycleEventArgs $args)
    {
        return $args;
    }

    public function setSerializer(DoctrineEntitySerializerInterface $serializer = null)
    {
        $this->serializer = (null === $serializer) ?
            $serializer = new DoctrineEntitySerializer() :
            $serializer;
    }

    protected function serializeObject($object)
    {
        return $this->serializer->toArray($object);
    }

    public function __call($name, $args)
    {
        if (!array_key_exists($name, $this->eventsMessages)) {
            throw new BayardRollingLogException("Calling an inexisting Method or unsuscribed Event: ".$name);
        }

        $eargs = $this->getLifecycleEventArgs($args[0]);
        $entity = $eargs->getObject();
        $entityName = $this->serializer->getSimpleClassName(get_class($entity));
        $entityManager = $eargs->getObjectManager();
        $entityManager->getUnitOfWork()->initializeObject($entity);

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
                $removing_values[$attr] = $this->serializer->objectAsName($value);
            } else {
                $removing_values[$attr] = $value;
            }
        }

        return $removing_values;
    }

    public function getContextForPostRemove(EntityManager $entityManager, $entity)
    {
        return $this->serializeObject($entity);

    }

    public function getContextForPostPersist(EntityManager $entityManager, $entity)
    {
        return $this->serializeObject($entity);
    }

    public function getContextForPostUpdate(EntityManager $entityManager, $entity)
    {
        $change_set = $entityManager->getUnitOfWork()->getEntityChangeSet($entity);

        foreach ($change_set as $attr => $values) {
            foreach ($values as $i => $value) {
                if (is_object($value)) {
                    $change_set[$attr][$i] = $this->serializer->objectAsName($value);
                }
            }
        }

        return $change_set;
    }



}