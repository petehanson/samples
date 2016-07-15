<?php

require_once("game.php");

class GameTest extends \PHPUnit_Framework_TestCase {

    protected $game;

    public function setUp() {

        $this->game = new Game();
    }

    public function testGutterGame() {

        $this->rollMany(0,20);
        $this->assertEquals(0,$this->game->score());
    }


    public function testAllOnes() {

        $this->rollMany(1,20);


        $this->assertEquals(20,$this->game->score());
    }

    private function rollMany($pinValue,$numberOfRolls) {

        for($i = 0; $i < $numberOfRolls; $i++) {
            $this->game->roll($pinValue);
        }
    }

    public function testOneSpare() {
        $this->game->roll(5);
        $this->game->roll(5);
        $this->game->roll(3);
        $this->rollMany(0,17);

        $this->assertEquals(16,$this->game->score());
    }
}