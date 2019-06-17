<?php

require_once __DIR__ . '/Distribution.php';

// ============================================================================
class ExponentialDistribution extends Distribution {
/*
    Exponential distribution favoring picks from the minimum end of the the 
    [min, max] range
*/
// ============================================================================

    // ------------------------------------------------------------------------
    protected function createGenerator(
    ) {
    // ------------------------------------------------------------------------
        // lambda = .04 creates a nice spectrum of values between 0 and 100
        return new MathPHP\Probability\Distribution\Continuous\Exponential(
            isset($this->options['lamdba']) ? $this->options['lamdba'] : .04
        );
    }

    // ------------------------------------------------------------------------
    public function getRandomValue(
        $decimals = 0
    ) {
    // ------------------------------------------------------------------------
        /* 
            Get value between 0 and roughly 100, with decreasing probability:

            |o
            | o
            |  o
            |    o
            |       o
            |            o
            |                   o
            ----------------------------ooooo
            0            50            100
        */
        $val = $this->generator->rand();
        
        // normalize val into [0, 1]
        $val = max(.01 * min(100, $val), 0.);
        
        // now get corresponding from min/max setting
        $val = $this->min + $val * ($this->max - $this->min);

        return $decimals === 0 ? intval($val) : round($val, $decimals);
    }
}