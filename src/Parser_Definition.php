<?php

namespace Haijin\Parser;

use Haijin\File_Path;
use Haijin\Dictionary;

class Parser_Definition
{
    protected $before_parsing_closure;
    protected $expressions_by_name;
    protected $methods;

    /// Initializing

    public function __construct()
    {
        $this->before_parsing_closure = null;

        $this->expressions_by_name = new Dictionary();

        $this->methods = new Dictionary();
    }

    /// Accessing

    public function get_before_parsing_closure()
    {
        return $this->before_parsing_closure;
    }

    public function get_expression_named($expression_name, $absent_closure = null)
    {
        return $this->expressions_by_name->at_if_absent(
                    $expression_name,
                    $absent_closure
                );
    }

    public function get_expressions_in( $expressions_names )
    {
        return $expressions_names->collect( function($expression_name) {

                return $this->get_expression_named( $expression_name );

            });
    }

    public function custom_method_at($method_name, $absent_closure = null)
    {
        return $this->methods->at_if_absent( $method_name, $absent_closure );
    }

    /// Defining

    public function define($closure)
    {
        $closure( $this );

        return $this;
    }

    /// DSL

    public function before_parsing($closure)
    {
        $this->before_parsing_closure = $closure;
    }

    public function expression($name, $definition_callable)
    {
        $expression = new Expression( $name );

        $definition_callable( $expression );

        $this->add_expression( $expression );
    }

    public function def( $method_name, $closure)
    {
        $this->methods[ $method_name ] = $closure;
    }

    protected function add_expression($expression)
    {
        $this->expressions_by_name[ $expression->get_name() ] = $expression;
    }

}