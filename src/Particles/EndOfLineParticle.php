<?php

namespace Haijin\Parser\Particles;

class EndOfLineParticle extends Particle
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
        return $parser->parseEolParticle($this);
    }

    /// Printing

    public function __toString()
    {
        return "eol()";
    }
}