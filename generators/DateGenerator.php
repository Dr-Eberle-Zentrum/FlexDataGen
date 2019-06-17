<?php
// ============================================================================
class DateGenerator extends FlexDataGen {
/*
    Produces dates in YYYY-MM-DD format in the given [min, max] range with 
    the given distribution
*/
// ============================================================================
    protected   $distribution,
                $minDt,
                $maxDt;

    // ------------------------------------------------------------------------
    public function __construct(
        $options = [/*
            min: number (required)
            max: number (required)
            distribution: class name (required)
        */]
    ) {
    // ------------------------------------------------------------------------
        parent::__construct($options);

        $this->minDt = new DateTime($this->options['min']);
        $this->maxDt = new DateTime($this->options['max']);

        $this->distribution = Helper::getInstanceArgs(
            $this->options['distribution'], [
                $this->minDt->getTimestamp(),
                $this->maxDt->getTimestamp()
            ]);
    }
    
    // ------------------------------------------------------------------------
    protected function generateValue(
        $row_no
    ) {
    // ------------------------------------------------------------------------
        $dt = new DateTime();
        $dt->setTimestamp($this->distribution->getRandomValue());
        return $dt->format('Y-m-d');
    }
}