<?php
// ============================================================================
abstract class FlexDataGen {
/*
    Base class for all random data generators. Each generator must implement
    the generateValue() function that retrieves a new random value based on the
    defined distribution function
*/
// ============================================================================

    protected $options;

    // ------------------------------------------------------------------------
    public function __construct(
        $options = []
    ) {
    // ------------------------------------------------------------------------
        $this->options = $options;
    }

    // ------------------------------------------------------------------------
    public function isNull(
    ) {
    // ------------------------------------------------------------------------
        return isset($this->options['nulls']) 
            && rand(0, 100) / 100. < $this->options['nulls'];
    }

    // ------------------------------------------------------------------------
    public function isUnique(
    ) {
    // ------------------------------------------------------------------------
        return isset($this->options['unique'])  
            && $this->options['unique'] === true;
    }

    // ------------------------------------------------------------------------
    public function getData(
        $count = 1
    ) {
    // ------------------------------------------------------------------------
        $data = [];
        for($i = 1; $i <= $count; $i++) {
            if($this->isNull()) {
                $data[] = null;
                continue;
            }

            while(true) {
                $v = $this->generateValue($i);

                if($this->isUnique() && $this->alreadyExists($v, $data))
                    continue;
                
                $this->postProcess($v);
                break;
            }

            $data[] = $v;
        }
        return $data;
    }

    // ------------------------------------------------------------------------
    // can be overridden if in_array doesn't fit the generator
    protected function alreadyExists(&$value, &$data) {
    // ------------------------------------------------------------------------
        return in_array($value, $data);
    }

    // ------------------------------------------------------------------------
    // should be invoked last if overridden
    protected function postProcess(&$value) {
    // ------------------------------------------------------------------------
        if(isset($this->options['postProcess']))
            $this->options['postProcess']($value);
    }

    // ------------------------------------------------------------------------
    // ABSTRACT FUNCTIONS
    // ------------------------------------------------------------------------

    protected abstract function generateValue($row_no);
}