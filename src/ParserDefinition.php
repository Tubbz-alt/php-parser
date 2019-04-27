<?php

namespace Haijin\Parser;

use Haijin\Dictionary;

class ParserDefinition
{
    protected $beforeParsingClosure;
    protected $expressionsByName;
    protected $methods;

    /// Initializing

    public function __construct()
    {
        $this->beforeParsingClosure = null;

        $this->expressionsByName = new Dictionary();

        $this->methods = new Dictionary();
    }

    /// Accessing

    public function getBeforeParsingClosure()
    {
        return $this->beforeParsingClosure;
    }

    public function getExpressionNamed($expressionName, $absentClosure = null)
    {
        return $this->expressionsByName->atIfAbsent(
            $expressionName,
            $absentClosure
        );
    }

    public function customMethodAt($methodName, $absentClosure = null)
    {
        return $this->methods->atIfAbsent($methodName, $absentClosure);
    }

    /// Defining

    public function define($closure)
    {
        $closure($this);

        return $this;
    }

    /// DSL

    public function beforeParsing($closure)
    {
        $this->beforeParsingClosure = $closure;
    }

    public function expression($name, $definitionCallable)
    {
        $expression = new Expression($name);

        $definitionCallable($expression);

        $this->addExpression($expression);
    }

    protected function addExpression($expression)
    {
        $this->expressionsByName[$expression->getName()] = $expression;
    }

    public function def($methodName, $closure)
    {
        $this->methods[$methodName] = $closure;
    }

}