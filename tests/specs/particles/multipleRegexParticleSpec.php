<?php

use Haijin\Parser\Errors\UnexpectedExpressionError;
use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When matching a multiple regex particle", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->describe("at the beginning of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->mRegex("/([0-9]+)/")->str("abc");

                    });

                    $exp->handler(function ($matches) {

                        return (int)$matches[0];

                    });

                });

            });

        });

        $this->describe("for each matched expression found", function () {

            $this->let("input", function () {
                return "123abc";
            });

            $this->it("evaluates the handler closure", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->be("===")->than(123);

            });

        });


        $this->describe("for an unexpected expression at the beginning", function () {

            $this->let("input", function () {
                return "z123";
            });

            $this->it("raises an error", function () {

                $this->expect(function () {

                    $this->parser->parseString($this->input);

                })->to()->raise(
                    UnexpectedExpressionError::class,
                    function ($error) {

                        $this->expect($error->getMessage())->to()->equal(
                            'Unexpected expression "z123". At line: 1 column: 1.'
                        );
                    });

            });

        });

        $this->describe("for an unexpected expression at following particle", function () {

            $this->let("input", function () {
                return "123z";
            });

            $this->it("raises an error", function () {

                $this->expect(function () {

                    $this->parser->parseString($this->input);

                })->to()->raise(
                    UnexpectedExpressionError::class,
                    function ($error) {

                        $this->expect($error->getMessage())->to()->equal(
                            'Unexpected expression "z". At line: 1 column: 4.'
                        );
                    });

            });

        });

    });

    $this->describe("in the midlle of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("abc")->mRegex("/([0-9]+)/")->str("cba");

                    });

                    $exp->handler(function ($matches) {

                        return (int)$matches[0];

                    });

                });

            });

        });

        $this->describe("for each matched expression found", function () {

            $this->let("input", function () {
                return "abc123cba";
            });

            $this->it("evaluates the handler closure", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->be("===")->than(123);

            });

        });


        $this->describe("for an unexpected expression at the beginning", function () {

            $this->let("input", function () {
                return "abczcba";
            });

            $this->it("raises an error", function () {

                $this->expect(function () {

                    $this->parser->parseString($this->input);

                })->to()->raise(
                    UnexpectedExpressionError::class,
                    function ($error) {

                        $this->expect($error->getMessage())->to()->equal(
                            'Unexpected expression "zcba". At line: 1 column: 4.'
                        );
                    });

            });

        });

        $this->describe("for an unexpected expression at following particle", function () {

            $this->let("input", function () {
                return "abc123z";
            });

            $this->it("raises an error", function () {

                $this->expect(function () {

                    $this->parser->parseString($this->input);

                })->to()->raise(
                    UnexpectedExpressionError::class,
                    function ($error) {

                        $this->expect($error->getMessage())->to()->equal(
                            'Unexpected expression "z". At line: 1 column: 7.'
                        );
                    });

            });

        });

    });

    $this->describe("at the end of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("abc")->mRegex("/([0-9]+)/");

                    });

                    $exp->handler(function ($matches) {

                        return (int)$matches[0];

                    });

                });

            });

        });

        $this->describe("for each matched expression found", function () {

            $this->let("input", function () {
                return "abc123";
            });

            $this->it("evaluates the handler closure", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->be("===")->than(123);

            });

        });


        $this->describe("for an unexpected expression", function () {

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
                            'Unexpected expression "z". At line: 1 column: 4.'
                        );
                    });

            });

        });

    });

});