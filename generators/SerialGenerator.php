<?php
// ============================================================================
class SerialGenerator extends FlexDataGen {
/*
    Produces incrementing integers starting from the given 'start' value
*/
// ============================================================================
    protected $curVal;

    // ------------------------------------------------------------------------
    public function __construct(
        $options = [/*
            start: number (required)
        */]
    ) {
    // ------------------------------------------------------------------------
        parent::__construct($options);
        $this->curVal = $this->options['start'];
    }

    
    // ------------------------------------------------------------------------
    protected function generateValue(
        $row_no
    ) {
    // ------------------------------------------------------------------------
        return $this->curVal++;
    }
}