<?php

namespace Haijin\Parser\Particles;

class EndOfStreamParticle extends Particle
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
        return $parser->parseEosParticle($this);
    }

    /// Printing

    public function __toString()
    {
        return "eos()";
    }
}