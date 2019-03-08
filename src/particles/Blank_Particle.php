<?php

namespace Haijin\Parser;

class Blank_Particle extends Particle
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
        return $parser->parse_blank_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "eos";
    }
}