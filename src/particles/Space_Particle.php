<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Space_Particle
{
    public function parse_with( $parser )
    {
        return $parser->parse_space( $this );
    }

    /// Printing

    public function print_string()
    {
        return "space()";
    }

}