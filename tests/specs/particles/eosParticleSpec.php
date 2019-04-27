<?php

use Haijin\Parser\Errors\UnexpectedExpressionError;
use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When matching an eos particle", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->describe("at the beginning of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->eos();

                    });

                    $exp->handler(function () {
                        return "parsed";
                    });

                });

            });

        });

        $this->it("with an empty string the expresion is valid", function () {

            $result = $this->parser->parseString("");

            $this->expect($result)->to()->equal("parsed");

        });

        $this->it("with a non empty string the expresion is invalid", function () {

            $this->expect(function () {

                $this->parser->parseString("123");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "123". At line: 1 column: 1.'
                    );
                });

        });

    });

    $this->describe("at the end of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("123")->eos();

                    });

                    $exp->handler(function () {
                        return "parsed";
                    });

                });

            });

        });

        $this->it("with a matching string the expresion is valid", function () {

            $result = $this->parser->parseString("123");

            $this->expect($result)->to()->equal("parsed");

        });

        $this->it("fails with a char", function () {

            $this->expect(function () {

                $this->parser->parseString("1234");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "4". At line: 1 column: 4.'
                    );
                });

        });

        $this->it("fails with a space", function () {

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

        $this->it("fails with a cr", function () {

            $this->expect(function () {

                $this->parser->parseString("123\n");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected "\n". At line: 1 column: 4.'
                    );
                });

        });

    });

});