<?php

use Haijin\Parser\Errors\UnexpectedExpressionError;
use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When matching an eol particle", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->describe("at the beginning of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->eol()->str("123");

                    });

                    $exp->handler(function () {
                        return "parsed";
                    });

                });

            });

        });

        $this->it("passes with a valid expression", function () {

            $result = $this->parser->parseString("\n123");

            $this->expect($result)->to()->equal("parsed");

        });

        $this->it("fails with an invalid expression", function () {

            $this->expect(function () {

                $this->parser->parseString("");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected end of stream. At line: 1 column: 1.'
                    );
                });

        });

    });

    $this->describe("in the middle of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("1")->eol()->str("2");

                    });

                    $exp->handler(function () {
                        return "parsed";
                    });

                });

            });

        });

        $this->it("passes with a valid expression", function () {

            $result = $this->parser->parseString("1\n2");

            $this->expect($result)->to()->equal("parsed");

        });

        $this->it("fails with an invalid expression", function () {

            $this->expect(function () {

                $this->parser->parseString("1 2");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression " 2". At line: 1 column: 2.'
                    );
                });

        });

    });

    $this->describe("at the end of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("123")->eol();

                    });

                    $exp->handler(function () {
                        return "parsed";
                    });

                });

            });

        });

        $this->it("with a cr the expresion is valid", function () {

            $result = $this->parser->parseString("123\n");

            $this->expect($result)->to()->equal("parsed");

        });

        $this->it("with an eos the expresion is valid", function () {

            $result = $this->parser->parseString("123");

            $this->expect($result)->to()->equal("parsed");

        });

        $this->it("with a non matching string the expresion is invalid", function () {

            $this->expect(function () {

                $this->parser->parseString("123 ");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression " ". At line: 1 column: 4.'
                    );
                });

        });

    });

});