<?php

// ============================================================================
/*
    Wrapper that allows access to all MathPHP continuous distribution classes
    that implement the method rand(), see:
    https://github.com/markrogoyski/math-php#probability---continuous-distributions
*/
class MathPHPGenerator extends FlexDataGen {
// ============================================================================
    protected $g;

    // ------------------------------------------------------------------------
    public function __construct(
        $options = [/*
            class: string (required)
            args: array (required)
            decimals: int (optional) = 0
        */]
    ) {
    // ------------------------------------------------------------------------
        if(!isset($options['decimals']))
            $options['decimals'] = 0;
        
        parent::__construct($options);

        $r = new ReflectionClass($options['class']);
        $this->g = $r->newInstanceArgs($options['args']);
    }
    
    // ------------------------------------------------------------------------
    protected function generateValue(
        $row_no
    ) {
    // ------------------------------------------------------------------------
        do {
            $v = $this->g->rand();
        } while(
            (isset($this->options['max']) && $v > $this->options['max'])
            || (isset($this->options['min']) && $v < $this->options['min'])
        );
        $v = round($v, $this->options['decimals']);
        return $this->options['decimals'] === 0 ? intval($v) : $v;
    }
}