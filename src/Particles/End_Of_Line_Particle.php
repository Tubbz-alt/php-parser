<?php

namespace Haijin\Parser\Particles;

class End_Of_Line_Particle extends Particle
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
        return $parser->parse_eol_particle( $this );
    }

    /// Printing

    public function __toString()
    {
        return "eol()";
    }
}