<?php

namespace Haijin\Parser;

use Haijin\OrderedCollection;
use Haijin\Parser\Particles\BlankParticle;
use Haijin\Parser\Particles\EndOfExpressionParticle;
use Haijin\Parser\Particles\EndOfLineParticle;
use Haijin\Parser\Particles\EndOfStreamParticle;
use Haijin\Parser\Particles\ProceduralParticle;
use Haijin\Parser\Particles\SpaceParticle;
use Haijin\Parser\Particles\SubExpressionParticle;

class Expression
{
    protected $name;
    protected $particles;
    protected $handlerClosure;

    /// Initializing

    public function __construct($name)
    {
        $this->name = $name;
        $this->handlerClosure = null;
        $this->particleSequences = new OrderedCollection();
        $this->particleSequences->add(
            new OrderedCollection()
        );
    }

    /// Accessing

    public function getName()
    {
        return $this->name;
    }

    public function getParticleSequences()
    {
        return $this->particleSequences;
    }

    public function getHandlerClosure()
    {
        return $this->handlerClosure;
    }

    /// DSL

    public function processor($closure)
    {
        $this->matcher(function () use ($closure) {

            $this->proc($closure);

        });
    }

    public function matcher($closure)
    {
        $closure($this);

        $this->appendEndOfSequenceToEachParticleSequence();
    }

    protected function appendEndOfSequenceToEachParticleSequence()
    {
        $this->particleSequences->eachDo(function ($particlesSequence) {

            $particlesSequence->add(
                new EndOfExpressionParticle()
            );

        });
    }

    public function proc($closure)
    {
        $this->addParticle(
            new ProceduralParticle($closure)
        );

        return $this;
    }

    protected function addParticle($particle)
    {
        $this->particleSequences->last()->add($particle);
    }

    public function mRegex($regexString)
    {
        $this->proc(function () use ($regexString) {

            $matches = [];

            \preg_match(
                $regexString . "A",
                $this->string,
                $matches,
                0,
                $this->contextFrame->charIndex
            );

            if (empty($matches)) {
                return false;
            }

            $this->skipChars(strlen($matches[0]));
            $this->setResult(array_slice($matches, 1));

            return true;

        });

        return $this;
    }

    public function regex($regexString)
    {
        $this->proc(function () use ($regexString) {

            $matches = [];

            \preg_match(
                $regexString . "A",
                $this->string,
                $matches,
                0,
                $this->contextFrame->charIndex
            );

            if (empty($matches)) {
                return false;
            }

            $this->skipChars(strlen($matches[0]));

            $this->setResult(
                isset($matches[1]) ? $matches[1] : $matches[0]
            );

            return true;

        });

        return $this;
    }

    public function str($string)
    {
        $this->proc(function () use ($string) {

            $stringLength = strlen($string);

            if ($this->contextFrame->charIndex + $stringLength
                >
                $this->stringLength
            ) {
                return false;
            }

            if (substr_compare(
                    $this->string,
                    $string,
                    $this->contextFrame->charIndex,
                    $stringLength
                )
                !=
                0
            ) {
                return false;
            }

            $this->skipChars($stringLength);

            return true;

        });

        return $this;
    }

    public function sym($string)
    {
        $this->proc(function () use ($string) {

            $stringLength = strlen($string);

            if ($this->contextFrame->charIndex + $stringLength
                >
                $this->stringLength
            ) {
                return false;
            }

            if (substr_compare(
                    $this->string,
                    $string,
                    $this->contextFrame->charIndex,
                    $stringLength
                )
                !=
                0
            ) {
                return false;
            }

            $this->skipChars(strlen($string));

            $this->setResult($string);

            return true;

        });

        return $this;
    }

    public function space()
    {
        $this->addParticle(
            new SpaceParticle()
        );

        return $this;
    }

    public function blank()
    {
        $this->addParticle(
            new BlankParticle()
        );

        return $this;
    }

    public function cr()
    {
        $this->proc(function () {

            if ($this->string[$this->contextFrame->charIndex] != "\n") {

                return false;

            }

            $this->skipChars(1);

            $this->newLine();

            return true;

        });

        return $this;
    }

    public function eos()
    {
        $this->addParticle(
            new EndOfStreamParticle()
        );

        return $this;
    }

    public function eol()
    {
        $this->addParticle(
            new EndOfLineParticle()
        );

        return $this;
    }

    public function opt($particle)
    {
        $this->particleSequences->last()->last()->beOptional();

        return $this;
    }

    public function or()
    {
        $this->particleSequences->add(
            new OrderedCollection()
        );

        return $this;
    }

    public function handler($closure)
    {
        $this->handlerClosure = $closure;
    }

    public function __call($methodName, $params)
    {
        return $this->exp($methodName);
    }

    public function exp($expressionName)
    {
        $this->addParticle(
            new SubExpressionParticle($expressionName)
        );

        return $this;
    }

}