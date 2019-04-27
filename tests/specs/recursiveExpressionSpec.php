<?php

use Haijin\Parser\Errors\UnexpectedExpressionError;
use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When matching recursive expressions", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->let("parserDefinition", function () {

        return (new ParserDefinition())->define(function ($parser) {

            $parser->expression("root", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->str("[")->integerList()->str("]");

                });

                $exp->handler(function ($integer) {

                    return $integer;

                });

            });

            $parser->expression("integerList", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->integer()->str(",")->integerList()
                        ->or()
                        ->integer();

                });

                $exp->handler(function ($integer, $list = null) {

                    if ($list === null) {
                        return [$integer];
                    } else {
                        return array_merge([$integer], $list);
                    }

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

    $this->describe("when the input matches a base expression", function () {

        $this->let("input", function () {
            return "[123]";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal([123]);

        });

    });

    $this->describe("when the input matches a recursive expression", function () {

        $this->let("input", function () {
            return "[123,321]";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal([123, 321]);

        });

    });

    $this->describe("for an unexpected expression at the beginning", function () {

        $this->let("input", function () {
            return "123,321]";
        });

        $this->it("raises an error", function () {

            $this->expect(function () {

                $this->parser->parseString($this->input);

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "123,321]". At line: 1 column: 1.'
                    );
                });

        });

    });

    $this->describe("for an unexpected expression after an expected expression", function () {

        $this->let("input", function () {
            return "[123 321]";
        });

        $this->it("raises an error", function () {

            $this->expect(function () {

                $this->parser->parseString($this->input);

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression " 321]". At line: 1 column: 5.'
                    );
                });

        });

    });

});