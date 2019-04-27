<?php

namespace Haijin\Parser;

use Haijin\FilePath;
use Haijin\OrderedCollection;
use Haijin\Parser\Errors\ExpressionNotFoundError;
use Haijin\Parser\Errors\MethodNotFoundError;
use Haijin\Parser\Errors\UnexpectedExpressionError;

class Parser
{
    public $string;
    public $stringLength;

    public $charIndex;
    public $lineIndex;
    public $columnIndex;

    /// Initializing

    public function __construct($parserDefinition)
    {
        $this->parserDefinition = $parserDefinition;

        $this->string = null;
        $this->stringLength = 0;

        $this->contextFrame = $this->newContextFrame();
        $this->framesStack = new OrderedCollection();

        $this->parsingError = null;

        $this->undefined = new \stdclass();
    }

    protected function newContextFrame()
    {
        return new ContextFrame();
    }

    /// Parsing inputs

    public function parse($file)
    {
        if (($file)) {
            $file = new FilePath($file);
        }

        return $this->parseString($file->readFileContents());
    }

    public function parseString($string)
    {
        $this->string = $string;
        $this->stringLength = strlen($this->string);

        $this->contextFrame->charIndex = 0;
        $this->contextFrame->lineIndex = 1;
        $this->contextFrame->columnIndex = 1;

        if ($this->hasBeforeParsingClosure()) {
            $this->evaluateBeforeParsingClosure();
        }

        $this->beginExpression("root");

        $this->doParsingLoop();

        return $this->contextFrame->getHandlerParams()[0];
    }

    /// Parsing particles loops

    protected function hasBeforeParsingClosure()
    {
        return $this->parserDefinition->getBeforeParsingClosure() !== null;
    }

    protected function evaluateBeforeParsingClosure()
    {
        $beforeParsingClosure = $this->parserDefinition->getBeforeParsingClosure();

        return $beforeParsingClosure->call($this);
    }

    /// Expressions stack

    protected function beginExpression($expressionName, $isOptional = false)
    {
        $this->saveCurrentContext();

        $this->contextFrame->setCurrentExpression(
            $this->getExpressionNamed($expressionName)
        );

        $this->contextFrame->setExpectedParticlesSequences(
            $this->contextFrame->getCurrentExpression()->getParticleSequences()
        );

        $this->contextFrame->setExpectedParticles(
            $this->contextFrame->nextExpectedParticlesSequence()
        );

        $this->contextFrame->setHandlerParams([]);
        $this->contextFrame->setExpressionResult(null);
        $this->contextFrame->setExpressionIsOptional($isOptional);
    }

    protected function saveCurrentContext()
    {
        $this->framesStack->add($this->contextFrame);

        $this->contextFrame = clone $this->contextFrame;
    }

    protected function getExpressionNamed($expressionName)
    {
        return $this->parserDefinition->getExpressionNamed(
            $expressionName,
            function () use ($expressionName) {

                $this->raiseExpressionNotFoundError($expressionName);

            });
    }

    /// Before parsing closure

    protected function raiseExpressionNotFoundError($expressionName)
    {
        throw new ExpressionNotFoundError(
            "The expression \"{$expressionName}\" was not found in this parser.",
            $expressionName,
            $this
        );
    }

    protected function doParsingLoop()
    {
        while ($this->contextFrame->hasExpectedParticles()) {
            $particle = $this->contextFrame->nextExpectedParticle();

            if ($this->atEndOfStream() && !$particle->matchesEos()) {

                $this->onUnexpectedParticle();
                continue;

            }

            $this->parseParticle($particle);
        }

        if ($this->parsingError !== null) {
            throw $this->parsingError;
        }

        if ($this->notEndOfStream()) {
            return $this->raiseUnexpectedExpressionError();
        }
    }

    /// Expressions

    /**
     * Returns true if the stream is beyond its last char, false otherwise.
     */
    protected function atEndOfStream()
    {
        return $this->contextFrame->charIndex >= $this->stringLength;
    }

    protected function onUnexpectedParticle()
    {
        if ($this->contextFrame->hasExpectedParticlesSequences()) {

            $this->beginNextParticlesSequence();

            return;

        }

        if ($this->parsingError === null) {
            $this->parsingError = $this->newUnexpectedExpressionError();
        }

        $this->endUnmatchedExpression();
    }

    protected function beginNextParticlesSequence()
    {
        $this->contextFrame->setExpectedParticles(
            $this->contextFrame->nextExpectedParticlesSequence()
        );

        $previousContext = $this->getPreviousContext();

        $this->contextFrame->charIndex = $previousContext->charIndex;
        $this->contextFrame->lineIndex = $previousContext->lineIndex;
        $this->contextFrame->columnIndex = $previousContext->columnIndex;

        $this->contextFrame->setHandlerParams([]);
    }

    protected function getPreviousContext()
    {
        return $this->framesStack->last();
    }

    /// Particles

    protected function newUnexpectedExpressionError()
    {
        if ($this->atEndOfStream()) {

            $expression = "end of stream";

        } elseif ($this->peekChar() == "\n") {

            $expression = '"\\n"';

        } else {

            $matches = [];

            preg_match(
                "/.*(?=\n?)/A",
                $this->string,
                $matches,
                0,
                $this->contextFrame->charIndex
            );

            $expression = "expression \"{$matches[0]}\"";
        }

        return new UnexpectedExpressionError(
            "Unexpected {$expression}. At line: {$this->currentLine()} column: {$this->currentColumn()}."
        );
    }

    /**
     * Returns the current char in the stream. Does not modify the stream.
     */
    public function peekChar()
    {
        return $this->peekCharAt(0);
    }

    /**
     * Returns the char at an $offset from its current position. Does not modify the stream.
     */
    public function peekCharAt($offset)
    {
        return $this->string[$this->contextFrame->charIndex + $offset];
    }

    /// Particle methods

    /**
     * Returns the current line index.
     *
     * Use this method for debugging and for error messages.
     */
    public function currentLine()
    {
        return $this->contextFrame->lineIndex;
    }

    /**
     * Returns the current column index in the current line.
     *
     * Use this method for debugging and for error messages.
     */
    public function currentColumn()
    {
        return $this->contextFrame->columnIndex;
    }

    protected function endUnmatchedExpression()
    {
        if ($this->contextFrame->expressionIsOptional()) {

            $this->restorePreviousContext();

            $this->contextFrame->addHandlerParam(null);

            $this->parsingError = null;

            return;
        }

        if ($this->framesStack->isEmpty()) {
            return;
        }

        $this->restorePreviousContext();

        $this->onUnexpectedParticle();
    }

    protected function restorePreviousContext()
    {
        $this->contextFrame = $this->framesStack->removeLast();
    }

    public function parseParticle($particle)
    {
        $this->contextFrame->setParticleResult($this->undefined);

        if ($particle->isOptional()) {
            $savedContext = clone $this->contextFrame;
        }

        $parsed = $particle->parseWith($this);

        if ($parsed) {

            $result = $this->contextFrame->getParticleResult();

            if ($result !== $this->undefined) {
                $this->contextFrame->addHandlerParam($result);
            }

        } else {

            if ($particle->isOptional()) {

                $this->contextFrame = $savedContext;

                $this->contextFrame->addHandlerParam(null);

            } else {

                $this->contextFrame->setParticleResult($this->undefined);

                $this->onUnexpectedParticle();

            }
        }
    }

    /**
     * Returns true if the stream has further chars, false otherwise.
     */
    protected function notEndOfStream()
    {
        return $this->contextFrame->charIndex < $this->stringLength;
    }

    protected function raiseUnexpectedExpressionError()
    {
        throw $this->newUnexpectedExpressionError();
    }

    /// Stream methods

    public function parseSubExpressionParticle($subExpressionParticle)
    {
        $this->beginExpression(
            $subExpressionParticle->getSubExpressionName(),
            $subExpressionParticle->isOptional()
        );

        return true;
    }

    public function parseProceduralParticle($proceduralParticle)
    {
        $proceduralParticleCallable = $proceduralParticle->getClosure();

        if (is_a($proceduralParticleCallable, \Closure::class)) {
            return $proceduralParticleCallable->call($this);
        } else {
            return $proceduralParticleCallable($this);
        }
    }

    public function parseBlankParticle($blankParticle)
    {
        if ($this->atEndOfStream()) {
            return true;
        }

        $char = $this->peekChar(0);

        while ($char == " " || $char == "\t" || $char == "\n") {

            if ($char == "\n") {
                $this->newLine();
            }

            $this->skipChars(1);

            if ($this->atEndOfStream()) {
                break;
            }

            $char = $this->peekChar(0);
        }

        return true;
    }

    /**
     * Increments by one the input line counter and resets the column counter to 1.
     *
     * Call this method when the parser encounters a "\n" character in order
     * to keep track of the correct line and column indices used in error messages.
     *
     * If this method is not properly called, the parser will still correctly parse
     * valid inputs but the error messages for invalid inputs will be invalid.
     */
    public function newLine()
    {
        $this->contextFrame->lineIndex += 1;
        $this->contextFrame->columnIndex = 1;
    }

    /**
     * Increments the stream pointer and the column counter by $n.
     *
     * Use these method to move backwards or forwards in the stream skipping chars.
     */
    public function skipChars($n)
    {
        $this->incrementCharIndexBy($n);
        $this->incrementColumnIndexBy($n);
    }

    public function incrementCharIndexBy($n)
    {
        $this->contextFrame->charIndex += $n;
    }

    public function incrementColumnIndexBy($n)
    {
        $this->contextFrame->columnIndex += $n;
    }

    public function parseSpaceParticle($spaceParticle)
    {
        if ($this->atEndOfStream()) {
            return true;
        }

        $char = $this->peekChar(0);

        while ($char == " " || $char == "\t") {

            $this->skipChars(1);

            if ($this->atEndOfStream()) {
                break;
            }

            $char = $this->peekChar(0);
        }

        return true;
    }

    public function parseEosParticle($eosParticle)
    {
        return $this->atEndOfStream();
    }

    public function parseEolParticle($eolParticle)
    {
        if ($this->atEndOfStream()) {
            return true;
        }

        if ($this->peekChar() == "\n") {
            $this->skipChars(1);
            $this->newLine();

            return true;
        }

        return false;
    }

    public function parseEndOfExpressionParticle($endOfExpressionParticle)
    {
        $this->endParticlesSequence();

        return true;
    }

    protected function endParticlesSequence()
    {
        $handlerClosure = $this->contextFrame->getCurrentExpression()->getHandlerClosure();

        if ($handlerClosure !== null) {

            if (is_a($handlerClosure, \Closure::class)) {

                $handlerResult = $handlerClosure->call(
                    $this,
                    ...$this->contextFrame->getHandlerParams()
                );

            } else {

                $handlerResult = $handlerClosure(
                    ...$this->contextFrame->getHandlerParams()
                );

            }

            $this->contextFrame->setExpressionResult($handlerResult);
        }

        $this->endMatchedExpression();

        return true;
    }

    protected function endMatchedExpression()
    {
        $currentContext = $this->contextFrame;

        $this->restorePreviousContext();

        $this->contextFrame->charIndex = $currentContext->charIndex;
        $this->contextFrame->lineIndex = $currentContext->lineIndex;
        $this->contextFrame->columnIndex = $currentContext->columnIndex;

        $this->contextFrame->addHandlerParam($currentContext->getExpressionResult());

        $this->parsingError = null;
    }

    /**
     * Returns the tail of the stream which has not been parsed yet.
     *
     * Use this method only to debug the parsing process. Using it for the actual
     * parsing of the input will probably be very inneficient.
     */
    public function currentString()
    {
        return \substr($this->string, $this->contextFrame->charIndex);
    }

    /// Custom methods

    /**
     * Returns the current char in the stream and moves forward the stream pointer by one.
     */
    public function nextChar()
    {
        $this->skipChars(1);

        return $this->peekCharAt(-1);
    }

    /// Raising errors

    /**
     * Sets the result of the particle to be an $object.
     *
     * The result of a particle can be any object, it does not need to be the actual parsed
     * input.
     */
    public function setResult($object)
    {
        $this->contextFrame->setParticleResult($object);
    }

    public function currentCharPos()
    {
        return $this->contextFrame->charIndex;
    }

    public function __call($methodName, $params)
    {
        $closure = $this->parserDefinition->customMethodAt($methodName, function () use ($methodName) {

            $this->raiseMethodNotFoundError($methodName);

        });

        if (is_a($closure, \Closure::class)) {
            return $closure->call($this, ...$params);
        } else {
            return $closure(...$params);
        }
    }

    protected function raiseMethodNotFoundError($methodName)
    {
        throw new MethodNotFoundError(
            "The method \"{$methodName}\" was not found in this parser.",
            $methodName,
            $this
        );
    }

}