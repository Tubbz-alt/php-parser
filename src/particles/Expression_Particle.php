<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Expression_Particle
{
    protected $expression_name;

    /// Initializing

    public function __construct($expression_name)
    {
        $this->expression_name = $expression_name;
    }

    /// Accessing

    public function get_expression_name()
    {
        return $this->expression_name;
    }

    /// Parsing

    public function parse_with( $parser )
    {
        return $parser->parse_expression_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "exp('$this->expression_name')";
    }

}