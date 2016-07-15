<?php

class Game {

    protected $score = 0;
    protected $rolls = array();

    public function roll($pins) {

        $this->rolls[] = $pins;
    }

    public function score() {

        $total = 0;

        $frameIndex = 0;

        for ($frame = 1; $frame <= 10; $frame++) {

            if ($this->isStrike($frameIndex)) {

                $total = $total + 10 + $this->strikeBonus($frameIndex);
                $frameIndex++;

            } else if ($this->isSpare($frameIndex)) {
               $total = $total + 10 + $this->spareBonus($frameIndex);
                $frameIndex += 2;
            } else {
                $total = $total + $this->sumOfBallsInFrame($frameIndex);
                $frameIndex += 2;
            }

        }

        return $total;
    }

    protected function strikeBonus($frameIndex) {
        return $this->rolls[$frameIndex + 1] + $this->rolls[$frameIndex + 2];
    }

    protected function spareBonus($frameIndex) {
       return  $this->rolls[$frameIndex + 2];
    }

    protected function sumOfBallsInFrame($frameIndex) {
        return $this->rolls[$frameIndex] + $this->rolls[$frameIndex + 1];
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