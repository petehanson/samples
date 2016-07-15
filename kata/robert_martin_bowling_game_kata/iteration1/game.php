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

            if ($this->isStrike($frameIndex)) {

                $total = $total + 10 + $this->rolls[$frameIndex + 1] + $this->rolls[$frameIndex + 2];

            } else if ($this->isSpare($frameIndex)) {
               $total = $total + 10 + $this->rolls[$frameIndex + 2];
                $frameIndex++;
            } else {
                $total = $total + $this->rolls[$frameIndex] + $this->rolls[$frameIndex + 1];
                $frameIndex++;
            }

        }

        return $total;
    }

    protected function isSpare($frameIndex) {
        if ($this->rolls[$frameIndex] + $this->rolls[$frameIndex + 1] == 10) {
            return true;
        } else {
            return false;
        }
    }

    protected function isStrike($frameIndex) {
        if ($this->rolls[$frameIndex] == 10) {
            return true;
        } else {
            return false;
        }
    }
}