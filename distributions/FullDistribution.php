<?php

require_once __DIR__ . '/Distribution.php';

// Helper class >>
class FullDistribution_Generator {
    private $cur, $max;
    public function __construct($min, $max) {
        $this->cur = $min;
        $this->max = $max;
    }
    public function rand() {
        return min($this->cur++, $this->max);
    }
} // <<

// ============================================================================
class FullDistribution extends Distribution {
/*
    Returns every number between min and max, 
    one after the other increasing order.

    !!! NEVER !!! set 'unique' to true when using this distribution
*/
// ============================================================================

    // ------------------------------------------------------------------------
    protected function createGenerator(
    ) {
    // ------------------------------------------------------------------------
        return new FullDistribution_Generator($this->min, $this->max);
    }

    // ------------------------------------------------------------------------
    public function getRandomValue(
        $decimals = 0
    ) {
    // ------------------------------------------------------------------------
        return intval($this->generator->rand());
    }
}