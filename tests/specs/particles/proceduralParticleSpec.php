<?php

use Haijin\Parser\Errors\UnexpectedExpressionError;
use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When matching a procedural particle", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->describe("at the begining of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->p()->str("123");

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

                $parser->expression("p", function ($exp) {

                    $exp->processor(function () {

                        if ($this->peekChar() == "#") {

                            $this->setResult("#");

                            $this->nextChar();

                            return true;

                        }

                        return false;

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

            });

        });

        $this->it("parses the input stream", function () {

            $result = $this->parser->parseString("#123");

            $this->expect($result)->to()->equal("#");

        });

        $this->it("fails if absent", function () {

            $this->expect(function () {

                $this->parser->parseString("z123");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "z123". At line: 1 column: 1.'
                    );
                });

        });

        $this->it("fails if the following particle fails", function () {

            $this->expect(function () {

                $this->parser->parseString("#12");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "12". At line: 1 column: 2.'
                    );
                });

        });

    });

    $this->describe("in the middle of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("123")->p()->str("321");

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

                $parser->expression("p", function ($exp) {

                    $exp->processor(function () {

                        if ($this->peekChar() == "#") {

                            $this->setResult("#");

                            $this->nextChar();

                            return true;

                        }

                        return false;

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

            });

        });

        $this->it("parses the input stream", function () {

            $result = $this->parser->parseString("123#321");

            $this->expect($result)->to()->equal("#");

        });

        $this->it("fails if absent", function () {

            $this->expect(function () {

                $this->parser->parseString("123321");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "321". At line: 1 column: 4.'
                    );
                });

        });

        $this->it("fails if the following particle fails", function () {

            $this->expect(function () {

                $this->parser->parseString("123#12");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "12". At line: 1 column: 5.'
                    );
                });

        });

    });

    $this->describe("at the end of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("123")->p();

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

                $parser->expression("p", function ($exp) {

                    $exp->processor(function () {

                        if ($this->peekChar() == "#") {

                            $this->setResult("#");

                            $this->nextChar();

                            return true;

                        }

                        return false;

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

            });

        });

        $this->it("parses the input stream", function () {

            $result = $this->parser->parseString("123#");

            $this->expect($result)->to()->equal("#");

        });

        $this->it("fails if absent", function () {

            $this->expect(function () {

                $this->parser->parseString("123");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected end of stream. At line: 1 column: 4.'
                    );
                });

        });

        $this->it("fails if does not match", function () {

            $this->expect(function () {

                $this->parser->parseString("123z");

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "z". At line: 1 column: 4.'
                    );
                });

        });

    });

    $this->describe("with a callable", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("123")->p();

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

                $parser->expression("p", function ($exp) {

                    $exp->processor(new Processor());

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

            });

        });

        $this->it("parses the input stream", function () {

            $result = $this->parser->parseString("123#");

            $this->expect($result)->to()->equal("#");

        });

    });

});

class Processor
{
    public function __invoke($parser)
    {
        if ('#' != $parser->currentString()) {
            throw new \Exception("Assertion failed");
        }

        if (3 != $parser->currentCharPos()) {
            throw new \Exception("Assertion failed");
        }

        if ($parser->peekChar() == "#") {

            $parser->setResult("#");

            $parser->nextChar();

            return true;

        }

        return false;
    }
}