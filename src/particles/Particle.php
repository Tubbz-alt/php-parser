<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;

class Particle
{
    protected $is_optional;
    protected $matches_eos;

    /// Initializing

    public function __construct()
    {
        $this->is_optional = false;
        $this->matches_eos = false;
    }

    public function be_optional()
    {
        $this->is_optional = true;
    }

    public function is_optional()
    {
        return $this->is_optional;
    }

    public function matches_eos()
    {
        return $this->matches_eos || $this->is_optional;
    }
}