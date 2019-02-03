<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Procedural_Particle
{
    protected $closure;
    protected $is_optional;

    /// Initializing

    public function __construct($closure)
    {
        $this->closure = $closure;
        $this->is_optional = false;
    }

    public function get_closure()
    {
        return $this->closure;
    }

    public function be_optional()
    {
        $this->is_optional = true;
    }

    public function is_optional()
    {
        return $this->is_optional;
    }

    /// Parsing

    public function parse_with( $parser )
    {
        return $parser->parse_procedural_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "procedural($closure)";
    }

}