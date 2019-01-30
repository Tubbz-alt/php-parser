<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class String_Particle
{
    protected $string;

    /// Initializing

    public function __construct($string)
    {
        $this->string = $string;
    }

    /// Accessing

    public function get_string()
    {
        return $this->string;
    }

    /// Parsing

    public function parse_with( $parser )
    {
        return $parser->parse_string_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "str('$this->string')";
    }

}