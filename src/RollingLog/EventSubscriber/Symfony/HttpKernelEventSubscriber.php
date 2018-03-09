<?php

namespace Bayard\RollingLog\EventSubscriber\Symfony;

//use Psr\Log\LoggerInterface;
use Bayard\RollingLog\EventSubscriber\AbstractLogSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
// use Bayard\RollingLog\Sanitizer\ContextSanitizer;
// use Bayard\RollingLog\Sanitizer\ArraySanitizerInterface;

class HttpKernelEventSubscriber extends AbstractLogSubscriber implements EventSubscriberInterface
{
    /**
     * @var ArraySanitizerInterface
     */
    protected $sanitizer;

    /**
     * @var Array
     */
    private static $requestType = [
        HttpKernelInterface::MASTER_REQUEST => 'MASTER_REQUEST',
        HttpKernelInterface::SUB_REQUEST => 'SUB_REQUEST',
    ];

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            KernelEvents::REQUEST => 'logRequest'
        );
    }

    public function logRequest(GetResponseEvent $event)
    {
        $context = $this->serializeRequest($event->getRequest());

        $context = array_merge($context, [
            'requestType' => static::$requestType[$event->getRequestType()]
        ]);

        $message = "Incoming Request";

        $this->logEvent($message, $context);
    }

    protected function serializeRequest($request)
    {
        $result = [
            'url' => $request-> getPathInfo(),
            'method' => $request->getMethod()
        ];

        switch (strtoupper($request->getMethod())) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $result['body'] = $request->request->all();
                $result['attributes'] = $request->attributes->all();
                break;

            case 'GET':
                $result['query'] = $request->query->all();
                break;

            default:
                $result['attributes'] = $request->attributes->all();
                break;
        }

        $result = array_merge($result, [
            'files' => $request->files->all(),
            'userInfo' => $request->getUserInfo(),
            'isXmlHttpRequest' => $request->isXmlHttpRequest(),
            'isSecure' => $request->isSecure() ? 'true' : 'false'
        ]);

        return $result;
    }

}