<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Sub_Expression_Particle
{
    protected $sub_expression_name;
    protected $is_optional;

    /// Initializing

    public function __construct($expression_name)
    {
        $this->sub_expression_name = $expression_name;
        $this->is_optional = false;
    }

    /// Accessing

    public function get_sub_expression_name()
    {
        return $this->sub_expression_name;
    }

    public function be_optional()
    {
        $this->is_optional = true;
    }

    public function is_optional()
    {
        return $this->is_optional;
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