<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Blank_Particle
{
    public function parse_with( $parser )
    {
        return $parser->parse_blank_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "blank()";
    }

}