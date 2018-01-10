<?php

namespace Bayard\RollingLog\Serializer;

use Bayard\RollingLog\Serializer\ArrayzerInterface;

use Doctrine\ORM\EntityManager;

trait DoctrineEntitySerializer
{

    /**
     * Serializes our Doctrine Entities
     *
     * This is the primary entry point, because it assists with handling collections
     * as the primary Object
     *
     * @param object $object The Object (Typically a Doctrine Entity) to convert to an array
     * @param integer $depth The Depth of the object graph to pursue
     * @param array $whitelist List of entity=>array(parameters) to convert
     * @param array $blacklist List of entity=>array(parameters) to skip
     * @return NULL|Array
     *
     */
    public function toArray(EntityManager $entityManager, $object, $depth = 1,$whitelist=array(), $blacklist=array())
    {
        // If we drop below depth 0, just return NULL
        if ($depth < 0){
            return NULL;
        }

        $anArray = array();

        $change_set = $entityManager->getUnitOfWork()->getEntityChangeSet($object);

        foreach ($change_set as $attr => $values) {
            foreach ($values as $i => $value) {
                if (is_object($value)) {
                    if($depth > 1)
                        $anArray[$attr] = $this->toArray($entityManager, $value, $depth-1, $whitelist, $blacklist);
                    else
                        $anArray[$attr] = $this->objectAsName($value);
                } else {
                    $anArray[$attr] = $value;
                }
            }
        }

        if(empty($whitelist))
           if (empty($blacklist))
                return $anArray;
            else
                return $this->blackListing($blacklist, $anArray);
        else
            return $this->whiteListing($whitelist, $anArray);

    }

    /**
     * [getSimpleClassName description]
     * @param  [type] $classname [description]
     * @return [type]            [description]
     */
    protected function getSimpleClassName($classname)
    {
        if ($pos = strrpos($classname, '\\'))
            return substr($classname, $pos + 1);

        return $pos;
    }

    /**
     * [blackListing description]
     * @param  [type] $blacklist [description]
     * @param  [type] $array     [description]
     * @return [type]            [description]
     */
    protected function blackListing($blacklist, $array)
    {
        $tmp = array();
        foreach ($array as $key => $value) {
            if(!in_array($key, $blacklist))
                $tmp[$key] = $value;
        }
        return $tmp;
    }

    /**
     * [whiteListing description]
     * @param  [type] $whitelist [description]
     * @param  [type] $array     [description]
     * @return [type]            [description]
     */
    protected function whiteListing($whitelist, $array)
    {
        $tmp = array();
        foreach ($array as $key => $value) {
            if(in_array($key, $whitelist))
                $tmp[$key] = $value;
        }
        return $tmp;
    }

    /**
     * [objectAsName description]
     * @param  [type] $object [description]
     * @return [type]         [description]
     */
    protected function objectAsName($object)
    {
        switch (true) {
            case method_exists($object, '__toString'):
                $result = $object->__toString();
                break;
            case $method_like_name = $this->methodLikeGetNameExists($object):
                $result = $object->$method_like_name();
                break;
            case method_exists($object, 'getSlug'):
                $result = $object->getSlug();
                break;
            case method_exists($object, 'getId'):
                $result = $object->getId();
                break;
            case method_exists($object, 'getLabel'):
                $result = $object->getLabel();
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


    /**
     * [methodLikeGetNameExists description]
     * @param  [type] $object [description]
     * @return [type]         [description]
     */
    protected function methodLikeGetNameExists($object)
    {
        $tab_method = array();
        $class_methods = get_class_methods($object);
        foreach ($class_methods as $method_name)
            if(strncmp($method_name, "get", 3) == 0)
                if (strpos($method_name, 'Name') !== false || strpos($method_name, 'name') !== false)
                    array_push($tab_method, $method_name);

        //voir les username/firstName/appName/fileName
        if(count($tab_method) !== 0)
            return in_array('getName', tab_method) ? "getName" : $tab_method[0];
        return false;
    }

        // /**
    //  * This does all the heavy lifting of actually converting to an array
    //  *
    //  * @param object $object The Object (Typically a Doctrine Entity) to convert to an array
    //  * @param integer $depth The Depth of the object graph to pursue
    //  * @param array $whitelist List of entity=>array(parameters) to convert
    //  * @param array $blacklist List of entity=>array(parameters) to skip
    //  * @return NULL|Array
    //  */
    // protected function arrayizor($anObject, $depth, $whitelist=array(), $blacklist=array())
    // {
    //     // Determine the next depth to use
    //     $nextDepth = $depth - 1;

    //     // Lets get our Class Name
    //     // @TODO: Making some assumptions that only objects get passed in, need error checking
    //     $clazzName = get_class($anObject);

    //     // Now get our reflection class for this class name
    //     $reflectionClass = new \ReflectionClass($clazzName);

    //     // Then grap the class properites
    //     $clazzProps = $reflectionClass->getProperties();

    //     if (is_a($anObject, 'Doctrine\ORM\Proxy\Proxy')){
    //         $parent = $reflectionClass->getParentClass();
    //         $clazzName = $parent->getName();
    //         $clazzProps = $parent->getProperties();
    //     }
    //     // A new array to hold things for us
    //     $anArray = array();

    //     // Lets loop through those class properties now
    //     foreach ($clazzProps as $prop){

    //         // If a Whitelist exists
    //         if (@count($whitelist[$clazzName]) > 0){
    //             // And this class property is not in it
    //             if (! @in_array($prop->name, $whitelist[$clazzName])){
    //                 // lets skip it.
    //                 continue;
    //             }
    //         // Otherwise, if a blacklist exists
    //         }elseif (@count($blacklist[$clazzName] > 0)){
    //             // And this class property is in it
    //             if (@in_array($prop->name, $blacklist[$clazzName])){
    //                 // lets skip it.
    //                 continue;
    //             }
    //         }

    //         // We know the property, lets craft a getProperty method
    //         $method_name = 'get' . ucfirst($prop->name) ;
    //         // And check to see that it exists for this object
    //         if (! method_exists($anObject, $method_name)){
    //             continue;
    //         }
    //         // It did, so lets call it!
    //         $aValue = $anObject->$method_name();
    //         // If it is an object, we need to handle that
    //         if (is_object($aValue)){
    //             // If it is a datetime, lets make it a string
    //             if (get_class($aValue) === 'DateTime'){
    //                 $anArray[$prop->name] = $aValue->format('Y-m-d H:i:s');
    //             // If it is a Doctrine Collection, we need to loop through it
    //             }elseif(get_class($aValue) ==='Doctrine\ORM\PersistentCollection'){
    //                 $collect = array();
    //                 foreach ($aValue as $val){
    //                     $collect[] = $this->toArray($val, $nextDepth, $whitelist, $blacklist);
    //                 }
    //                 $anArray[$prop->name] = $collect;

    //             // Otherwise, we can simply make it an array
    //             }else{
    //                 $anArray[$prop->name] = $this->toArray($aValue, $nextDepth, $whitelist, $blacklist);
    //             }
    //         // Otherwise, we just use the base value
    //         }else{

    //             $anArray[$prop->name] = $val;
    //         }
    //     }
    //     // All done, send it back!
    //     return $anArray;
    // }
}