<?php

namespace Haijin\Parser\Errors;

use Haijin\Errors\Haijin_Error;

class Method_Not_Found_Error extends Haijin_Error
{
    protected $method_name;
    protected $parser;

    /// Initializing

    public function __construct($message, $method_name, $parser)
    {
        parent::__construct( $message );

        $this->method_name = $method_name;
        $this->parser = $parser;
    }

    /// Accesing

    public function get_method_name()
    {
        return $this->method_name;
    }

    public function get_parser()
    {
        return $this->parser;
    }
}