<?php
require_once __DIR__ . '/CategoryGenerator.php';

// ============================================================================
class ForeignKeyGenerator extends CategoryGenerator {
/*
    Produces foreign key references to a 'column' in another 'table' (which 
    has to be filled prior to using this generator).
*/
// ============================================================================
    protected $distribution;

    // ------------------------------------------------------------------------
    public function __construct(
        $options = [/*
            table: string (required)
            column: string (required)
            distribution: class name (required)
            dataSet: current dataset (required)
        */]
    ) {
    // ------------------------------------------------------------------------
        $options['source'] = $options['dataSet']->getColumnValues(
            $options['table'], $options['column']
        );
        
        parent::__construct($options);
    }
}