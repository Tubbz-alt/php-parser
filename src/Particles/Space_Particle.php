<?php

namespace Haijin\Parser\Particles;

class Space_Particle extends Particle
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
        return $parser->parse_space_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "eos";
    }
}