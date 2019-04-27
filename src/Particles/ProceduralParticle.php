<?php

namespace Haijin\Parser\Particles;

class ProceduralParticle extends Particle
{
    protected $closure;

    /// Initializing

    public function __construct($closure)
    {
        parent::__construct();

        $this->closure = $closure;
    }

    public function getClosure()
    {
        return $this->closure;
    }

    /// Parsing

    public function parseWith($parser)
    {
        return $parser->parseProceduralParticle($this);
    }

    /// Printing

    public function __toString()
    {
        return "procedural(\$closure)";
    }

}