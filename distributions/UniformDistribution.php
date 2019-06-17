<?php

require_once __DIR__ . '/Distribution.php';

// ============================================================================
class UniformDistribution extends Distribution {
/*
    Generates uniformly distributed numbers in the [min, max] range
*/
// ============================================================================

// ------------------------------------------------------------------------
    protected function createGenerator(
    ) {
    // ------------------------------------------------------------------------
        return new MathPHP\Probability\Distribution\Continuous\Uniform(
            $this->min, 
            $this->max
        );
    }

    // ------------------------------------------------------------------------
    public function getRandomValue(
        $decimals = 0
    ) {
    // ------------------------------------------------------------------------
        $val = $this->generator->rand();
        return $decimals === 0 ? intval($val) : round($val, $decimals);
    }
}