<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Carriage_Return_Particle
{
    public function parse_with( $parser )
    {
        return $parser->parse_cr_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "cr()";
    }

}