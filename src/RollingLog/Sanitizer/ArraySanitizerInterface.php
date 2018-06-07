<?php

namespace Bayard\RollingLog\Sanitizer;

interface ArraySanitizerInterface
{
    /**
     * [sanitize description]
     * @param  array  $array [description]
     * @return array        [description]
     */
    public function sanitize(array $array);
}
