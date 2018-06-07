<?php

namespace Bayard\RollingLog\Sanitizer;

class ContextSanitizer implements ArraySanitizerInterface
{
    /**
     * [$scramble description]
     * @var string
     */
    const SCRAMBLE = '##########';


    /**
     * [sanitizeContext description]
     * @param  Array $array [description]
     * @return Array         [description]
     */
    public function sanitize(array $array)
    {
        foreach ($array as $key => $value) {
            if (empty($value)) {
                unset($array[$key]);
            }
        }

        $array = $this->sanitizePassword($array);

        return $array;
    }

    /**
     * [sanitize description]
     * @param  Array  $array [description]
     * @return Array        [description]
     */
    public function sanitizePassword(array $array)
    {
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                if (stripos($key, 'password') !== false) {
                    foreach ($item as $subkey => $subval) {
                        $array[$key][$subkey] = static::SCRAMBLE;
                    }
                } else {
                    $array[$key] = $this->sanitizePassword($item);
                }
            } else if (stripos($key, 'password') !== false) {
                $array[$key] = static::SCRAMBLE;
            }
        }

        return $array;
    }
}