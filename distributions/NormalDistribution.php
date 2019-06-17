<?php

require_once __DIR__ . '/Distribution.php';

// ============================================================================
class NormalDistribution extends Distribution {
/*
    Generates normally distributed values in [min, max] ranged from a random
    number in [0, 1] with a default mean of 0.5 and a default std. dev. of 0.1
*/
// ============================================================================

    // ------------------------------------------------------------------------
    protected function createGenerator(
    ) {
    // ------------------------------------------------------------------------
        // we use these parameters, which creates a nice spectrum of values between 0 and 1
        return new MathPHP\Probability\Distribution\Continuous\Normal(
            isset($this->options['mean']) ? $this->options['mean'] : .5, 
            isset($this->options['std_dev']) ? $this->options['std_dev'] : .1
        );
    }

    // ------------------------------------------------------------------------
    public function getRandomValue(
        $decimals = 0
    ) {
    // ------------------------------------------------------------------------
        $val = $this->generator->rand();
        
        // respect bounds [0, 1]
        $val = max(min(1., $val), 0.);

        // now get corresponding from min/max setting
        $val = $this->min + $val * ($this->max - $this->min);

        $val = round($val, $decimals);

        return $decimals === 0 ? intval($val) : $val;
    }
}