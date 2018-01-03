# Log on Doctrine Events

## Usage Requirements

You need Doctrine as a dependency of your project, so your composer.json file should look like this :

```json
{
    "require": {
        "doctrine/orm": "^2.5"
    }
}
```

## Enabling Doctrine Logger

### generic PHP

Like you can see in the Doctrine documentation in chapter [The Event System](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#listening-and-subscribing-to-lifecycle-events) :

```php
<?php
use Doctrine\ORM\EntityManager;
use Bayard\RollingLog\EventSubscriber\LogDoctrineEventSubscriber;

// You need obviously to get you logger. The LogFactory here is just an example
$myPsrLogger = MyPsrLogFactory::create('mylog');
// So you can add the EventSubscriber LogDoctrineEventSubscriber
$eventManager = $entityManager->getEventManager();
$logDoctrineEventSubscriber = new LogDoctrineEventSubscriber($myPsrLogger);
$eventManager->addEventSubscriber();
// and here we are ! :)
?>
```


### Symfony framework

As you can find in [Symfony DOC](http://symfony.com/doc/current/doctrine/event_listeners_subscribers.html#configuring-the-listener-subscriber)

```yaml
# Monolog configuration
monolog:
    handlers:
        rolling:
            type: stream
            path: '%kernel.logs_dir%/%kernel.environment%/doctrine.log'
            level: info
            channels: [doctrine]

# LogDoctrineEventSubscriber configuration
services:
    bayardlog.event_subscriber.doctrine:
        class: Bayard\RollingLog\EventSubscriber\LogDoctrineEventSubscriber
        arguments: ['@monolog.logger.doctrine']
        tags:
            - { name: doctrine.event_subscriber, connection: default }
```
