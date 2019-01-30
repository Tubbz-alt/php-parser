<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Symbol_Particle
{
    protected $symbol_string;

    /// Initializing

    public function __construct($symbol_string)
    {
        $this->symbol_string = $symbol_string;
    }

    /// Accessing

    public function get_symbol_string()
    {
        return $this->symbol_string;
    }

    /// Parsing

    public function parse_with( $parser )
    {
        return $parser->parse_symbol_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "sym('$this->symbol_string')";
    }

}