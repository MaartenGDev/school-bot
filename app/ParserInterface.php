<?php
namespace App;


interface ParserInterface
{


    /**
     * Parses something
     *
     * @param string $data The to parse string
     *
     * @return array
     */
    public function parse($data);
}