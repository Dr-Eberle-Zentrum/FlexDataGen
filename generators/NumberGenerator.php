<?php
// ============================================================================
class NumberGenerator extends FlexDataGen {
/*
    Generates a random number in the given [min,max] range using the given
    distribution and desired number of decimal digits.
*/
// ============================================================================
    protected $distribution;

    // ------------------------------------------------------------------------
    public function __construct(
        $options = [/*
            min: number (required)
            max: number (required)
            decimals: int (optional) = 0
            distribution: class name (required)
        */]
    ) {
    // ------------------------------------------------------------------------
        parent::__construct($options);

        if(!isset($options['decimals'])) {
            $this->options['decimals'] = 0;
        }
        
        $this->distribution = Helper::getInstanceArgs(
            $this->options['distribution'], [
                $this->options['min'],
                $this->options['max'],
                $this->options
            ]);
    }
    
    // ------------------------------------------------------------------------
    protected function generateValue(
        $row_no
    ) {
    // ------------------------------------------------------------------------
        return $this->distribution->getRandomValue($this->options['decimals']);
    }
}