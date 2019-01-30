<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Regex_Particle
{
    protected $regex_string;

    /// Initializing

    public function __construct($regex_string)
    {
        $this->regex_string = $regex_string;
    }

    /// Accessing

    public function get_regex_string()
    {
        return $this->regex_string;
    }

    /// Parsing

    public function parse_with( $parser )
    {
        return $parser->parse_regex_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "regex('$this->regex_string')";
    }

}