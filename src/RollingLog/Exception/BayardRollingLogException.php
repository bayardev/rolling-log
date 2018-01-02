<?php

namespace Bayard\RollingLog\Exception;

class BayardRollingLogException extends \Exception implements BayardRollingLogExceptionInterface
{
    public static function badClassEventParameterMsg($event, $parameter)
    {
        $msg = 'Event: ' . $event . ' called with bad arguments ! ';
        $msg .= 'Expect object of class LifecycleEventArgs . ';
        $msg .= (is_object($parameter))
            ? 'Object of class ' . get_class($args[1])
            : gettype($parameter);
        $msg .= ' given.';

        return new self($msg);
    }

    public static function parameterNotFound($parameter)
    {
        $msg = 'Parameter ' . $parameter . ' NOT FOUND !';

        return new self($msg);
    }

    public static function invalidClass($expected_class, $got_class)
    {
        $msg = "Expected Object of class " . $expected_class . ". " .
            "Got '" . $got_class . "' instead !";

        return new self($msg);
    }

    public static function entityPropertyNotFound($entity, $property)
    {
        $msg = "The entity '".$entity."' does not have a property named '".$property."'!";

        return new self($msg);
    }
}