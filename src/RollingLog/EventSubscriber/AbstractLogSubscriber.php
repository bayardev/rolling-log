<?php

namespace Bayard\RollingLog\EventSubscriber;

use Psr\Log\LoggerInterface;
//use Bayard\RollingLog\Serializer\ArrayTransformerInterface;
use Bayard\RollingLog\Sanitizer\ContextSanitizer;
use Bayard\RollingLog\Sanitizer\ArraySanitizerInterface;

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
        $this->setSanitizer();
    }

    public function setSanitizer(ArraySanitizerInterface $sanitizer = null)
    {
        $this->sanitizer = (null === $sanitizer) ?
            $sanitizer = $this->getDefaultSanitizer() :
            $sanitizer;
    }

    protected function getDefaultSanitizer()
    {
        return new ContextSanitizer();
    }

    protected function logEvent($message, array $context = array(), $level = self::DEFAULT_LOGLEVEL)
    {
        $context = $this->sanitizer->sanitize($context);

        $this->logger->log($level, $message, $context);
    }


}