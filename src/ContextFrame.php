<?php

namespace Haijin\Parser;

use Haijin\Errors\HaijinError;
use Haijin\OrderedCollection;

class ContextFrame
{
    public $charIndex;
    public $lineIndex;
    public $columnIndex;

    protected $currentExpression;
    protected $currentParticlesSequences;
    protected $expectedParticles;

    protected $handlerParams;
    protected $expressionResult;
    protected $expressionIsOptional;
    protected $particleResult;

    /// Initializing

    public function __construct()
    {
        $this->charIndex = null;
        $this->lineIndex = 1;
        $this->columnIndex = 1;

        $this->currentExpression = null;
        $this->currentParticlesSequences = new OrderedCollection();
        $this->expectedParticles = new OrderedCollection();

        $this->handlerParams = [];
        $this->expressionResult = null;
        $this->expressionIsOptional = false;
    }

    /// Expected expression

    public function getCurrentExpression()
    {
        return $this->currentExpression;
    }

    public function setCurrentExpression($expression)
    {
        $this->currentExpression = $expression;
    }

    public function hasCurrentExpression()
    {
        return $this->currentExpression !== null;
    }

    /// Expected particles sequence

    public function setExpectedParticles($particlesSequence)
    {
        $this->expectedParticles = clone $particlesSequence;
    }

    public function hasExpectedParticles()
    {
        return $this->expectedParticles->notEmpty();
    }

    public function nextExpectedParticle()
    {
        return $this->expectedParticles->removeFirst();
    }

    /// Expected particles sequences

    public function setExpectedParticlesSequences($particlesSequences)
    {
        $this->currentParticlesSequences = clone $particlesSequences;
    }

    public function hasExpectedParticlesSequences()
    {
        return $this->currentParticlesSequences->notEmpty();
    }

    public function nextExpectedParticlesSequence()
    {
        return $this->currentParticlesSequences->removeFirst();
    }

    /// Handler parameters

    public function getHandlerParams()
    {
        return $this->handlerParams;
    }

    public function setHandlerParams($params)
    {
        $this->handlerParams = $params;
    }

    public function addHandlerParam($object)
    {
        return $this->handlerParams[] = $object;
    }

    /// Expression result

    public function getExpressionResult()
    {
        return $this->expressionResult;
    }

    public function setExpressionResult($object)
    {
        $this->expressionResult = $object;
    }

    public function setExpressionIsOptional($isOptional)
    {
        $this->expressionIsOptional = $isOptional;
    }

    public function expressionIsOptional()
    {
        return $this->expressionIsOptional;
    }

    /// Particle result

    public function getParticleResult()
    {
        return $this->particleResult;
    }

    public function setParticleResult($object)
    {
        $this->particleResult = $object;
    }

    /// Error handling

    public function __get($property)
    {
        throw new HaijinError("The Context_Frame does not accept dymamic properties.");
    }

    public function __set($property, $value)
    {
        throw new HaijinError("The Context_Frame does not accept dymamic properties.");
    }
}