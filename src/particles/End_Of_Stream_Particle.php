<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class End_Of_Stream_Particle extends Particle
{
    /// Initializing

    public function __construct()
    {
        parent::__construct();

        $this->matches_eos = true;
    }

    /// Parsing

    public function parse_with( $parser )
    {
        return $parser->parse_eos_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "eos";
    }
}