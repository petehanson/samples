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


            if ($i % 2 == 0) {
                $roll1 = $this->rolls[$i];
                $roll2 = $this->rolls[$i + 1];

                if ($roll1 + $roll2 == 10) {  // have spare
                    $total = $total + $roll1 + $roll2 + $this->rolls[$i + 2];
                    $i++;
                } else {
                    $total += $this->rolls[$i];
                }
            } else {
                $total += $this->rolls[$i];
            }
        }

        return $total;
    }
}