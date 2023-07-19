<?php

namespace App\Helpers;

class Text
{
    public static function clean(string $textToClean) : string
    {
        return preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $textToClean);
    }
}
