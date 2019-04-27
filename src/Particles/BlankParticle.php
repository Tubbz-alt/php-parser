<?php

namespace Haijin\Parser\Particles;

class BlankParticle extends Particle
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
        return $parser->parseBlankParticle($this);
    }

    /// Printing

    public function __toString()
    {
        return "blank()";
    }
}