<?php
// ============================================================================
class CategoryGenerator extends FlexDataGen {
/*
    Produces values from a given set of categories, which can be provided either
    as a filename from which the range of categories is obtained line by line
    or from a static array
*/
// ============================================================================
    protected $distribution;
    protected $list;
    protected static $fileSources = [];

    // ------------------------------------------------------------------------
    public function __construct(
        $options = [/*
            source: string|array (string: filename, array: flat list)
            distribution: array (settings)
        */]
    ) {
    // ------------------------------------------------------------------------
        parent::__construct($options);
        if(is_array($options['source'])) { // flat list
            $this->list = $options['source'];
        }
        else if(is_string($options['source'])) { // list from file
            $fileName = $options['source'];
            if(!isset(self::$fileSources[$fileName]))
                $this->loadFromFile($fileName);
            $this->list = self::$fileSources[$fileName];
        }
        $this->createDistribution();
    }

    // ------------------------------------------------------------------------
    protected function loadFromFile(
        $fileName
    ) {
    // ------------------------------------------------------------------------
        self::$fileSources[$fileName] = [];
        $list = &self::$fileSources[$fileName];
        foreach(Helper::lineByLine($fileName) as $line)
            $list[] = $line;
    }

    // ------------------------------------------------------------------------
    protected function createDistribution(
    ) {
    // ------------------------------------------------------------------------
        $this->distribution = Helper::getInstanceArgs(
            $this->options['distribution'], [ 
                0, 
                count($this->list) - 1, 
                $this->options
            ]
        );
    }
    
    // ------------------------------------------------------------------------
    protected function postProcess(
        &$value
    ) {
    // ------------------------------------------------------------------------
        if($this->isUnique()) {
            array_splice($this->list, $this->last_index, 1);
            $this->distribution->adjustMax(-1);
        }
        
        parent::postProcess($value);
    }
    
    // ------------------------------------------------------------------------
    protected function generateValue(
        $row_no
    ) {
    // ------------------------------------------------------------------------
        $this->last_index = $this->distribution->getRandomValue();
        return $this->list[$this->last_index];
    }
}