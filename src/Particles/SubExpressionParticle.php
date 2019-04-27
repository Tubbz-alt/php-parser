<?php

namespace Haijin\Parser\Particles;

class SubExpressionParticle extends Particle
{
    protected $subExpressionName;

    /// Initializing

    public function __construct($expressionName)
    {
        parent::__construct();

        $this->subExpressionName = $expressionName;
    }

    /// Accessing

    public function getSubExpressionName()
    {
        return $this->subExpressionName;
    }

    /// Parsing

    public function parseWith($parser)
    {
        return $parser->parseSubExpressionParticle($this);
    }

    /// Printing

    public function __toString()
    {
        return "exp('$this->subExpressionName')";
    }

}