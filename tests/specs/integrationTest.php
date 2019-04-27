<?php

use Haijin\Errors\FileNotFoundError;
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

                    $exp->str("[")->space()->integerList()->space()->str("]");

                });

                $exp->handler(function ($integers) {

                    return array_sum($integers);

                });

            });

            $parser->expression("integerList", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->integer()->space()->str(",")->space()->integerList()
                        ->or()
                        ->integer();

                });

                $exp->handler(function ($integer, $list = null) {

                    if ($list === null) {

                        return [$integer];

                    }

                    return array_merge([$integer], $list);

                });

            });

            $parser->expression("integer", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->opt($exp->sym("-"))->regex("/([0-9]+)/");

                });

                $exp->handler(function ($negative, $integerString) {

                    if ($negative === null) {
                        return (int)$integerString;
                    } else {
                        return -(int)$integerString;
                    }

                });

            });

        });

    });

    $this->describe("when the input matches a base expression", function () {

        $this->let("input", function () {
            return "[1]";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal(1);

        });

    });

    $this->describe("when the input matches a base expression with spaces", function () {

        $this->let("input", function () {
            return "[ 1 ]";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal(1);

        });

    });

    $this->describe("when the input matches a recursive expression", function () {

        $this->let("input", function () {
            return "[1,2,3,4]";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal(10);

        });

    });

    $this->describe("when the input matches a recursive expression with spaces", function () {

        $this->let("input", function () {
            return "[ 1 , 2 , 3 , 4 ]";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal(10);

        });

    });

    $this->describe("when the input matches a negative integer", function () {

        $this->let("input", function () {
            return "[ 1 , -2 , 3 , 4 ]";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal(6);

        });

    });

    $this->describe("for an unexpected expression at the beginning", function () {

        $this->let("input", function () {
            return "1,2,3,4]";
        });

        $this->it("raises an error", function () {

            $this->expect(function () {

                $this->parser->parseString($this->input);

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "1,2,3,4]". At line: 1 column: 1.'
                    );
                });

        });

    });

    $this->describe("for an unexpected expression after an expected expression", function () {

        $this->let("input", function () {
            return "[1 2 3 4]";
        });

        $this->it("raises an error", function () {

            $this->expect(function () {

                $this->parser->parseString($this->input);

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "2 3 4]". At line: 1 column: 4.'
                    );
                });

        });

    });

    $this->describe("parse a file", function () {

        $this->it("raises an error", function () {

            $result = $this->parser->parse('tests/samples/input.txt');

            $this->expect($result)->to()->equal(10);

        });

        $this->it("raises an error if the file does not exist", function () {

            $this->expect(function () {

                $this->parser->parse('tests/samples/missing-file.txt');

            })->to()->raise(
                FileNotFoundError::class
            );

        });

    });

});