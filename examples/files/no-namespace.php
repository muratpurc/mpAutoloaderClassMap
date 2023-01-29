<?php

interface NoNsInterface {}

trait NoNsTrait {}

class NoNsClass {

    public function getAnonInstance()
    {
        return new class implements NoNsInterface {
        };
    }

}
