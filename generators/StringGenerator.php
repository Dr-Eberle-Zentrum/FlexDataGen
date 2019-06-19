<?php
// ============================================================================
class StringGenerator extends FlexDataGen {
/*
    Produces a string based on the given alphabet with the given minimum and
    maximum length; the characters are picked from the alphabet usign the given
    distribution
*/
// ============================================================================
    protected $distribution;

    // ------------------------------------------------------------------------
    public function __construct(
        $options = [/*
            alphabet: string (required)
            minLength: int (required)
            maxLength: int (required)
            distribution: string (required) 
        */]
    ) {
    // ------------------------------------------------------------------------
        parent::__construct($options);
        $this->createDistribution();
    }

    // ------------------------------------------------------------------------
    public function createDistribution(
    ) {
    // ------------------------------------------------------------------------
        $this->distribution = Helper::getInstanceArgs(
            $this->options['distribution'], [ 
                0,
                mb_strlen($this->options['alphabet']) - 1,
                $this->options
            ]
        );
    }
    
    // ------------------------------------------------------------------------
    protected function generateValue(
        $row_no
    ) {
    // ------------------------------------------------------------------------
        $len = $this->getLength();
        $val = '';
        for($i = 0; $i < $len; $i++) {
            $val .= $this->options['alphabet'][$this->distribution->getRandomValue()];
        }
        return $val;
    }

    // ------------------------------------------------------------------------
    protected function getLength(
    ) {
    // ------------------------------------------------------------------------
        return 
            intval(
                round(
                    rand(
                        $this->options['minLength'], 
                        $this->options['maxLength']
                    )
                    , 0
                )
            );
    }
}