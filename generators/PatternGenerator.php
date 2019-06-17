<?php
// ============================================================================
class PatternGenerator extends FlexDataGen {
/*
    Produces a string based on the given pattern (analogous to sprintf()); each
    placeholder in the pattern is generated by the corresponding generator in
    the provided list of generators
*/
// ============================================================================
    protected $generators;

    // ------------------------------------------------------------------------
    public function __construct(
        $options = [/*
            pattern: string (required)
            generators: array (required)
            unique: bool (optional) = false
        */]
    ) {
    // ------------------------------------------------------------------------
        parent::__construct($options);
        $this->createGenerators();
    }

    // ------------------------------------------------------------------------
    protected function createGenerators(
    ) {
    // ------------------------------------------------------------------------
        $this->generators = [];
        foreach($this->options['generators'] as $generator) {
            $this->generators[] = Helper::getInstance(
                $generator['generator'], 
                $generator['options']
            );
        }
    }
    
    // ------------------------------------------------------------------------
    protected function generateValue(
        $row_no
    ) {
    // ------------------------------------------------------------------------
        $args = [];
        foreach($this->generators as $generator) {
            $args[] = $generator->getData(1)[0];
        }
        return sprintf($this->options['pattern'], ...$args);
    }
}