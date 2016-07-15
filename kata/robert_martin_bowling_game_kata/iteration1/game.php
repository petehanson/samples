<?php

class Game {

    protected $score = 0;
    protected $rolls = array();

    public function roll($pins) {

        $this->rolls[] = $pins;
    }

    public function score() {

        $total = 0;

        for ($i =0; $i < count($this->rolls); $i++) {

            // make a frame

            $frame = array();
            $frame[] = $this->rolls[$i];
            $i++;
            $frame[] = $this->rolls[$i];


            if ($this->isSpare($frame)) {
               $total = $total + $this->rolls[$i + 1];
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