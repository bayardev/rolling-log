<?php

namespace Bayard\RollingLog\Sanitizer;

interface ArraySanitizerInterface
{
    /**
     * [sanitize description]
     * @param  Array  $array [description]
     * @return Array        [description]
     */
    public function sanitize(Array $array);
}