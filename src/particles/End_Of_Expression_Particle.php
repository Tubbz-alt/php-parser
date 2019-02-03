<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class End_Of_Expression_Particle
{
    /// Parsing

    public function parse_with( $parser )
    {
        return $parser->parse_end_of_expression_particle( $this );
    }

    public function is_optional()
    {
        return false;
    }

    /// Printing

    public function print_string()
    {
        return "end-of-expression";
    }

}