<?php

use Haijin\Parser\Errors\ExpressionNotFoundError;
use Haijin\Parser\Errors\UnexpectedExpressionError;
use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;


$spec->describe("When matching an expression particle", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->describe("at the beginning of a expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->integer()->str("abc");

                    });

                    $exp->handler(function ($integer) {

                        return $integer;

                    });

                });

                $parser->expression("integer", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->regex("/([0-9]+)/");

                    });

                    $exp->handler(function ($integerString) {

                        return (int)$integerString;

                    });

                });

            });

        });

        $this->it("passes for a valid expression", function () {

            $result = $this->parser->parseString("123abc");

            $this->expect($result)->to()->be("===")->than(123);

        });

        $this->describe("fails if the sub-expression does not match", function () {

            $this->let("input", function () {
                return "abcz";
            });

            $this->it("raises an error", function () {

                $this->expect(function () {

                    $this->parser->parseString($this->input);

                })->to()->raise(
                    UnexpectedExpressionError::class,
                    function ($error) {

                        $this->expect($error->getMessage())->to()->equal(
                            'Unexpected expression "abcz". At line: 1 column: 1.'
                        );
                    });

            });

        });

        $this->describe("fails if the following particle does not match", function () {

            $this->let("input", function () {
                return "123a";
            });

            $this->it("raises an error", function () {

                $this->expect(function () {

                    $this->parser->parseString($this->input);

                })->to()->raise(
                    UnexpectedExpressionError::class,
                    function ($error) {

                        $this->expect($error->getMessage())->to()->equal(
                            'Unexpected expression "a". At line: 1 column: 4.'
                        );
                    });

            });

        });

    });

    $this->describe("in the middle of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("abc")->integer()->str("cba");

                    });

                    $exp->handler(function ($integer) {

                        return $integer;

                    });

                });

                $parser->expression("integer", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->regex("/([0-9]+)/");

                    });

                    $exp->handler(function ($integerString) {

                        return (int)$integerString;

                    });

                });

            });

        });

        $this->it("passes for a valid expression", function () {

            $result = $this->parser->parseString("abc123cba");

            $this->expect($result)->to()->be("===")->than(123);

        });

        $this->describe("fails if the sub-expression does not match", function () {

            $this->it("raises an error", function () {

                $this->expect(function () {

                    $this->parser->parseString("abczabc");

                })->to()->raise(
                    UnexpectedExpressionError::class,
                    function ($error) {

                        $this->expect($error->getMessage())->to()->equal(
                            'Unexpected expression "zabc". At line: 1 column: 4.'
                        );
                    });

            });

        });

    });

    $this->describe("at the end of a expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("abc")->integer();

                    });

                    $exp->handler(function ($integer) {

                        return $integer;

                    });

                });

                $parser->expression("integer", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->regex("/([0-9]+)/");

                    });

                    $exp->handler(function ($integerString) {

                        return (int)$integerString;

                    });

                });

            });

        });

        $this->it("passes for a valid expression", function () {

            $result = $this->parser->parseString("abc123");

            $this->expect($result)->to()->be("===")->than(123);

        });

        $this->describe("fails if the sub-expression does not match", function () {

            $this->it("raises an error", function () {

                $this->expect(function () {

                    $this->parser->parseString("zabc");

                })->to()->raise(
                    UnexpectedExpressionError::class,
                    function ($error) {

                        $this->expect($error->getMessage())->to()->equal(
                            'Unexpected expression "zabc". At line: 1 column: 1.'
                        );
                    });

            });

        });

    });

    $this->describe("that is not defined", function () {

        $this->let("parserDefinition", function () {

            return new ParserDefinition();

        });

        $this->it("raises an error", function () {

            $this->expect(function () {

                $this->parser->parseString("123abc");

            })->to()->raise(
                ExpressionNotFoundError::class,
                function ($error) {

                    $this->expect($error->getMethodName())->to()->equal('root');

                    $this->expect($error->getParser())->to()
                        ->be('===')->than($this->parser);
                });

        });

    });

});