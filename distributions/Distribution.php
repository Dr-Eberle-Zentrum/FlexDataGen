<?php

// ============================================================================
abstract class Distribution {
/*
    Base class for all Distributions
*/
// ============================================================================

    protected   $generator;
    protected   $min, $max, $options;

    // ------------------------------------------------------------------------
    public function __construct(
        $min, 
        $max,
        $options = []
    ) {
    // ------------------------------------------------------------------------
        $this->min = $min;
        $this->max = $max;
        $this->options = $options;
        $this->generator = $this->createGenerator();
    }

    // ------------------------------------------------------------------------
    public function adjustMax(
        $by
    ) {
    // ------------------------------------------------------------------------
        $this->max += $by;
        $this->max = max($this->min + 1, $this->max);
        $this->generator = $this->createGenerator();
    }


    // ------------------------------------------------------------------------
    public abstract function getRandomValue($decimals = 0);
    protected abstract function createGenerator();
}