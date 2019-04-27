<?php

namespace Haijin\Parser\Particles;

class Particle
{
    protected $isOptional;
    protected $matchesEos;

    /// Initializing

    public function __construct()
    {
        $this->isOptional = false;
        $this->matchesEos = false;
    }

    public function beOptional()
    {
        $this->isOptional = true;
    }

    public function isOptional()
    {
        return $this->isOptional;
    }

    public function matchesEos()
    {
        return $this->matchesEos || $this->isOptional;
    }
}