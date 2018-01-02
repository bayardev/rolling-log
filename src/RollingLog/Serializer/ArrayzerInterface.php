<?php

namespace Bayard\RollingLog\Serializer;

interface ArrayzerInterface
{
    /**
     * Serializes an Object to Array
     *
     * @param object $object The Object (Typically a Doctrine Entity) to convert to an array
     * @param integer $depth The Depth of the object graph to pursue
     * @param array $whitelist List of entity=>array(parameters) to convert
     * @param array $blacklist List of entity=>array(parameters) to skip
     * @return NULL|Array
     *
     */
    public function toArray($object, $depth = 1,$whitelist=array(), $blacklist=array());
}