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

    public function setSerializer(ArrayTransformerInterface $serializer)
    {
        $this->serializer = $serializer;
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
}