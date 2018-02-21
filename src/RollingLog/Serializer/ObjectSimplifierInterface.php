<?php

namespace Bayard\RollingLog\Serializer;

interface ObjectSimplifierInterface
{
     /**
     * recovers a simple class name in doctrine class name
     * @param  String $classname doctrine class name
     * @return String            Simple class name
     */
    public function getSimpleClassName($classname);

    /**
     * Function research a first information of object with one his exist method, else serialize object
     * @param  Object $object       Object where search method
     * @return String|Array         First information or serialize object
     */
    public function objectAsName($object);
}