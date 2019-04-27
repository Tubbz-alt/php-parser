<?php

namespace Haijin\Parser\Particles;

class SpaceParticle extends Particle
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
        return $parser->parseSpaceParticle($this);
    }

    /// Printing

    public function __toString()
    {
        return "space()";
    }
}