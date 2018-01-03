<?php

namespace Bayard\RollingLog\EventSubscriber;

use Psr\Log\LoggerInterface;
//use Bayard\RollingLog\Serializer\ArrayzerInterface;
//use Bayard\RollingLog\Serializer\DoctrineEntitySerializer;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerBuilder;

abstract class AbstractLogSubscriber
{
    const DEFAULT_LOGLEVEL = 'INFO';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(LoggerInterface $logger, ArrayTransformerInterface $serializer = null)
    {
        $this->logger = $logger;
        $this->serializer = (null === $serializer) ?
            $serializer = SerializerBuilder::create()->build() :
            $serializer;
    }

    protected function logEvent($message, array $context = array(), $level = self::DEFAULT_LOGLEVEL)
    {
        $this->logger->log($level, $message, $context);
    }

    protected function getSimpleClassName($classname)
    {
        if ($pos = strrpos($classname, '\\'))
            return substr($classname, $pos + 1);

        return $pos;
    }

    protected function serializeObject($object)
    {
        return $this->serializer->toArray($object);
    }

    protected function objectAsName($object)
    {
        switch (true) {
            case method_exists($object, '__toString'):
                $result = $object->__toString();
                break;
            case method_exists($object, 'getName'):
                $result = $object->getName();
                break;
            case $method_like_name = $this->methodLikeGetNameExists($object):
                $result = $object->$method_like_name();
                break;
            case method_exists($object, 'getId'):
                $result = $object->getId();
                break;
            case method_exists($object, 'getId'):
                $result = $object->getId();
                break;
            case ($object instanceof \DateTime):
                $result = $object->format(\DateTime::ATOM);
                break;
            default:
                $result = $this->serializeObject($object);
                //$result = get_class($object);
                break;
        }

        return $result;
    }

    protected function methodLikeGetNameExists($object)
    {
        $class_methods = get_class_methods($object);
        foreach ($class_methods as $method_name) {
            if (strpos($method_name, 'Name') !== false) {
                return $method_name;
            }
        }

        return false;
    }
}