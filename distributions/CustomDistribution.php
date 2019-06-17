<?php

require_once __DIR__ . '/Distribution.php';

// ============================================================================
class CustomDistribution extends Distribution {
/*
    This distribution allows segregating the possible value interval into a 
    discrete set of relative probabilities (options.probabilityDistribution).

    First, a value from the set of relative probability is randomly
    based on the probability distribution in the set. Then the set size is
    mapped to the desired value space [min, max] to retrieve a value.

    Note this distribution works best for generating integers (e.g. to generate 
    an index in an array). The interpretation of decimals might be misleading.
    Optimally the size of the probability distribution array is equal to the
    [min, max] range of the desired random value
*/
// ============================================================================
    protected   $setSize,
                $sumWeights,
                $probabilityDistr;
    /*
        $options must be provided upon initialization. In this example a
        kind of discrete inverted exponential distribution is generated, so it
        will be more likely that a random value is drawn from the max end of
        the desired range
        [
            'probabilityDistribution' => [ 1, 3, 6, 9, 12, 21, 45]
        ]
    */
    // ------------------------------------------------------------------------
    public function __construct(
        $min,
        $max,
        $options = []
    ) {
    // ------------------------------------------------------------------------
        $this->probDistr = $options['probabilityDistribution'];
        $this->sumWeights = array_sum($this->probDistr);
        $this->setSize = count($this->probDistr);
        parent::__construct($min, $max, $options);
    }

    // ------------------------------------------------------------------------
    protected function createGenerator(
    ) {
    // ------------------------------------------------------------------------
        return new MathPHP\Probability\Distribution\Continuous\Uniform(
            0, $this->sumWeights
        );
    }

    // ------------------------------------------------------------------------
    public function getRandomValue(
        $decimals = 0
    ) {
    // ------------------------------------------------------------------------
        // get a random value from the weighted array
        $val = $this->generator->rand();

        // compute probability index and internal offset
        $curSum = 0;
        $randomIndex = -1;
        $offset = -1;
        foreach($this->probDistr as $index => $weight) {
            if($index === $this->setSize - 1 || $val < $curSum + $weight) {
                $lower = $curSum;
                $upper = $curSum + $weight;
                $offset = ($val - $lower) / floatval($weight);
                $randomIndex = $index;
                break;
            }
            $curSum += $weight;
        }

        // since we're rounding later we need to extend the value range, 
        // depending on the desired precision
        $roundOffset = 1 / pow(10, $decimals); // 1 for 0, .1 for 1, etc.

        // now calculate the full possible value range between min and max
        $valueRange = $this->max - $this->min + $roundOffset;

        // we divide the total value range by the number of categories in the distribution
        $stepSize = $valueRange / $this->setSize;
        
        // compute mininimum value in the value range
        $minValue = $stepSize * $randomIndex;

        // within the current value window we weigh all values equal, and apply the offset
        $value = $minValue + $stepSize * $offset;

        // return rounded value in extended [min, max] range
        $value = min($this->max, round($this->min - $roundOffset / 2 + $value, $decimals));
        return $decimals === 0 ? intval($value) : $value;
    }
}