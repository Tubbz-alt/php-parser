<?php

use Haijin\Parser\Errors\UnexpectedExpressionError;
use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When matching a particle among several", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->let("parserDefinition", function () {

        return (new ParserDefinition())->define(function ($parser) {

            $parser->expression("root", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->integer()->or()->alpha()->or()->str("#");

                });

                $exp->handler(function ($integerOrAlpha = null) {

                    return $integerOrAlpha;

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

            $parser->expression("alpha", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->regex("/([a-z]+)/");

                });

                $exp->handler(function ($alphaString) {

                    return $alphaString;

                });

            });

        });

    });

    $this->describe("when the input matches the first expression", function () {

        $this->let("input", function () {
            return "123";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->be("===")->than(123);

        });

    });

    $this->describe("when the input matches the second expression", function () {

        $this->let("input", function () {
            return "abc";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal("abc");

        });

    });

    $this->describe("when the input matches the third expression", function () {

        $this->let("input", function () {
            return "#";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->be()->null();

        });

    });

    $this->describe("for an unexpected expression at the beginning", function () {

        $this->let("input", function () {
            return "+123";
        });

        $this->it("raises an error", function () {

            $this->expect(function () {

                $this->parser->parseString($this->input);

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "+123". At line: 1 column: 1.'
                    );
                });

        });

    });

    $this->describe("for an unexpected expression after an expected expression", function () {

        $this->let("input", function () {
            return "123+";
        });

        $this->it("raises an error", function () {

            $this->expect(function () {

                $this->parser->parseString($this->input);

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "+". At line: 1 column: 4.'
                    );
                });

        });

    });

});