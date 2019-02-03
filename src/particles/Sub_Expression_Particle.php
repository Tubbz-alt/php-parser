<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Sub_Expression_Particle extends Particle
{
    protected $sub_expression_name;

    /// Initializing

    public function __construct($expression_name)
    {
        parent::__construct();

        $this->sub_expression_name = $expression_name;
    }

    /// Accessing

    public function get_sub_expression_name()
    {
        return $this->sub_expression_name;
    }

    /// Parsing

    public function parse_with( $parser )
    {
        return $parser->parse_sub_expression_particle( $this );
    }

    /// Printing

    public function print_string()
    {
        return "exp('$this->sub_expression_name')";
    }

}