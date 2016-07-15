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

                $total = $total + 10 + $this->strikeBonus($frameIndex);

            } else if ($this->isSpare($frameIndex)) {
               $total = $total + 10 + $this->spareBonus($frameIndex);
                $frameIndex++;
            } else {
                $total = $total + $this->sumOfBallsInFrame($frameIndex);
                $frameIndex++;
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