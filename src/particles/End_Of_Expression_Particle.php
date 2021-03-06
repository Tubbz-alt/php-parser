<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class End_Of_Expression_Particle extends Particle
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
        return $parser->parse_end_of_expression_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "end-of-expression";
    }
}