<?php

namespace Haijin\Parser\Errors;

use Haijin\Errors\HaijinError;

class MethodNotFoundError extends HaijinError
{
    protected $methodName;
    protected $parser;

    /// Initializing

    public function __construct($message, $methodName, $parser)
    {
        parent::__construct($message);

        $this->methodName = $methodName;
        $this->parser = $parser;
    }

    /// Accesing

    public function getMethodName()
    {
        return $this->methodName;
    }

    public function getParser()
    {
        return $this->parser;
    }
}