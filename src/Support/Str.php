<?php

namespace SAP\PDLibrary\Support;

class Str
{
    /**
     * Pad the text with $lenght 0s on left.
     * 
     * @param string $text 
     * @param int $lenght
     *  
     * @return string
     */
    public static function pad($text, $lenght)
    {
        return str_pad($text, $lenght, '0', STR_PAD_LEFT);
    }
}