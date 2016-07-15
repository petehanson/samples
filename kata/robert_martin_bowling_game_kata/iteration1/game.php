<?php

class Game {

    protected $score = 0;
    protected $rolls = array();

    public function roll($pins) {

        $this->rolls[] = $pins;
    }

    public function score() {

        $total = 0;

        for ($frameIndex =0; $frameIndex < count($this->rolls); $frameIndex++) {

            // make a frame

            $frame = array();
            $frame[] = $this->rolls[$frameIndex];
            $frameIndex++;
            $frame[] = $this->rolls[$frameIndex];


            if ($this->isSpare($frame)) {
               $total = $total + $this->rolls[$frameIndex + 1];
            }

            $total = $total + $frame[0] + $frame[1];

        }

        return $total;
    }

    protected function isSpare($frame) {
        if ($frame[0] + $frame[1] == 10) {
            return true;
        } else {
            return false;
        }
    }
}