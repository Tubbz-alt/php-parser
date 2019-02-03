<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Procedural_Particle extends Particle
{
    protected $closure;

    /// Initializing

    public function __construct($closure)
    {
        parent::__construct();

        $this->closure = $closure;
    }

    public function get_closure()
    {
        return $this->closure;
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