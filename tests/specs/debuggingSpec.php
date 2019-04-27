<?php

use Haijin\Errors\HaijinError;
use Haijin\Parser\ContextFrame;
use Haijin\Parser\Particles\BlankParticle;
use Haijin\Parser\Particles\EndOfExpressionParticle;
use Haijin\Parser\Particles\EndOfLineParticle;
use Haijin\Parser\Particles\EndOfStreamParticle;
use Haijin\Parser\Particles\ProceduralParticle;
use Haijin\Parser\Particles\SpaceParticle;
use Haijin\Parser\Particles\SubExpressionParticle;

$spec->describe("When debugging expressions", function () {

    $this->it("returns a End_Of_Line_Particle print string", function () {

        $particle = new EndOfLineParticle();

        $this->expect((string)$particle)
            ->to()->equal("eol()");

    });

    $this->it("returns a Blank_Particle print string", function () {

        $particle = new BlankParticle();

        $this->expect((string)$particle)
            ->to()->equal("blank()");

    });

    $this->it("returns a End_Of_Line_Particle print string", function () {

        $particle = new EndOfLineParticle();

        $this->expect((string)$particle)
            ->to()->equal("eol()");

    });

    $this->it("returns a End_Of_Stream_Particle print string", function () {

        $particle = new EndOfStreamParticle();

        $this->expect((string)$particle)
            ->to()->equal("eos()");

    });

    $this->it("returns a Procedural_Particle print string", function () {

        $particle = new ProceduralParticle(function () {
        });

        $this->expect((string)$particle)
            ->to()->equal("procedural(\$closure)");

    });

    $this->it("returns a Space_Particle print string", function () {

        $particle = new SpaceParticle();

        $this->expect((string)$particle)
            ->to()->equal("space()");

    });

    $this->it("returns a Sub_Expression_Particle print string", function () {

        $particle = new SubExpressionParticle('subExp');

        $this->expect((string)$particle)
            ->to()->equal("exp('subExp')");

    });

    $this->it("returns a End_Of_Expression_Particle print string", function () {

        $particle = new EndOfExpressionParticle();

        $this->expect((string)$particle)
            ->to()->equal("end-of-expression");

    });

    $this->describe('a Context_Frame', function () {

        $this->it("raises an error when getting a property", function () {

            $this->expect(function () {

                $contextFrame = new ContextFrame();
                $contextFrame->prop;

            })->to()->raise(
                HaijinError::class,
                function ($error) {
                    $this->expect($error->getMessage())->to()
                        ->equal('The Context_Frame does not accept dymamic properties.');
                }
            );

        });

        $this->it("raises an error when setting a property", function () {

            $this->expect(function () {

                $contextFrame = new ContextFrame();
                $contextFrame->prop = 123;

            })->to()->raise(
                HaijinError::class,
                function ($error) {
                    $this->expect($error->getMessage())->to()
                        ->equal('The Context_Frame does not accept dymamic properties.');
                }
            );

        });

    });

});