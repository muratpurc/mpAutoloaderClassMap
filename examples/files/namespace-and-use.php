<?php

namespace Foo\Bar;

use Exception;

interface NsAndUseInterface
{
}

trait NsAndUseTrait
{
}

class NsAndUseClass
{
    public function __construct()
    {
        throw new Exception('Instantiation not allowed!');
    }
}
