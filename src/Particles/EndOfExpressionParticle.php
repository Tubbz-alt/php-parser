<?php

namespace Haijin\Parser\Particles;

class EndOfExpressionParticle extends Particle
{
    /// Initializing

    public function __construct()
    {
        parent::__construct();

        $this->matchesEos = true;
    }

    /// Parsing

    public function parseWith($parser)
    {
        return $parser->parseEndOfExpressionParticle($this);
    }

    /// Printing

    public function __toString()
    {
        return "end-of-expression";
    }
}