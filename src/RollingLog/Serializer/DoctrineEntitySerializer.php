<?php

namespace Bayard\RollingLog\Serializer;

use Bayard\RollingLog\Serializer\DoctrineEntitySerializerInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DoctrineEntitySerializer implements DoctrineEntitySerializerInterface
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
    public function toArray($object, $depth = 1,$whitelist=array(), $blacklist=array())
    {
        // If we drop below depth 0, just return NULL
        if ($depth < 0){
            return NULL;
        }

        $anArray = array();

        foreach(get_class_methods($object) as $method)
        {
            if(strncmp($method, "get", 3) == 0)
            {
                $value = $object->$method();
                $attr = lcfirst(substr($method, 3));
                if(is_object($value)) 
                {
                    if($depth > 1)
                    {
                        switch (true) 
                        {
                            case ($value instanceof PersistentCollection):
                                foreach ($value->getValues() as $tmpValue) 
                                {
                                    $anArray[$attr][] = $this->toArray($tmpValue, $depth-1, $whitelist, $blacklist);
                                }
                                break;
                            case ($value instanceof \DateTime):
                                $anArray[$attr] = $value->format(\DateTime::ATOM);
                                break;
                            case ($value instanceof UploadedFile):
                                break;
                            default:
                                $anArray[$attr] = $this->toArray($value, $depth-1, $whitelist, $blacklist);
                                break;
                        }
                    }
                    else{
                        $anArray[$attr] = $this->objectAsName($value);
                    }
                }
                else
                {
                    $anArray[$attr] = $value;
                }
            }
        }
        // exit();
        if(empty($whitelist))
           if (empty($blacklist))
                return $anArray;
            else
                return $this->blackListing($blacklist, $anArray);
        else
            return $this->whiteListing($whitelist, $anArray);

    }

    /**
     * recovers a simple class name in doctrine class name
     * @param  String $classname doctrine class name
     * @return String            Simple class name
     */
    public function getSimpleClassName($classname)
    {
        if ($pos = strrpos($classname, '\\'))
            return substr($classname, $pos + 1);

        return $pos;
    }

    /**
     * function take of blacklist elements of the array
     * @param  Array $blacklist  elements to take of
     * @param  Array $array      traeted array
     * @return Array             Array whithout blacklist 
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
     * function take of the element are not in the array
     * @param  Array $whitelist  elements to keep
     * @param  Array $array      traeted array
     * @return Array             Array with only whitelist
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
     * Function research a first information of object with one his exist method, else serialize object
     * @param  Object $object       Object where search method
     * @return String|Array         First information or serialize object
     */
    public function objectAsName($object)
    {
        switch (true) {
            case method_exists($object, '__toString'):
                $result = $object->__toString();
                break;
            case method_exists($object, 'getId'):
                $result = $object->getId();
                break;
            case ($object instanceof PersistentCollection):
                $result = $this->persistentCollectionToArrayAsId($object);
                break;
            case $method_like_name = $this->methodLikeGetNameExists($object):
                $result = $object->$method_like_name();
                break;
            case method_exists($object, 'getSlug'):
                $result = $object->getSlug();
                break;
            case method_exists($object, 'getLabel'):
                $result = $object->getLabel();
                break;
            case method_exists($object, 'getAlt'):
                $result = $object->getAlt();
                break;
            case method_exists($object, 'geturl'):
                $result = $object->geturl();
                break;
            case ($object instanceof \DateTime):
                $result = $object->format(\DateTime::ATOM);
                break;
            default:
                $result = $this->toArray($object);
                break;
        }
        return $result;
    }


    /**
     * Function research method to return a name of class
     * @param  Object $object       Object where search method name
     * @return String|false         method to return a name of class or false
     */
    protected function methodLikeGetNameExists($object)
    {
        $tab_method = array();
        $class_methods = get_class_methods($object);
        foreach ($class_methods as $method_name)
            if(strncmp($method_name, "get", 3) == 0)
                if (strpos($method_name, 'Name') !== false || strpos($method_name, 'name') !== false)
                    $tab_method[] = $method_name;

        //voir les username/firstName/appName/fileName
        if(count($tab_method) !== 0)
        {
            switch (true) {
                case in_array('getName', $tab_method):
                    return "getName";
                case in_array('getUserName', $tab_method):
                    return "getUserName";
                case in_array('getUsername', $tab_method):
                    return "getUsername";
                case in_array('getFirstName', $tab_method):
                    return "getFirstName";
                case in_array('getFileName', $tab_method):
                    return "getFileName";
                default:
                    return $tab_method[0];
            }
        }
        return false;
    }


    /**
     * [persistentCollectionToArrayAsId description]
     * @param  PersistentCollection $object [description]
     * @return [type]                       [description]
     */
    protected function persistentCollectionToArrayAsId(PersistentCollection $object)
    {
        $tmp = array();
        foreach ($object->getValues() as $object) {
            if(method_exists($object, "getId"))
                $tmp["id"][] = $object->getId();
            else
                $tmp[] = $this->objectAsName($value);
        }
        return $tmp;
    }
}