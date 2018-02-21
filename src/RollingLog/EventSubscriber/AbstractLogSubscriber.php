<?php

namespace Bayard\RollingLog\EventSubscriber;

use Psr\Log\LoggerInterface;
use Bayard\RollingLog\Serializer\ArrayTransformerInterface;

abstract class AbstractLogSubscriber
{
    const DEFAULT_LOGLEVEL = 'INFO';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function logEvent($message, array $context = array(), $level = self::DEFAULT_LOGLEVEL)
    {
        $this->logger->log($level, $message, $context);
    }

    
}