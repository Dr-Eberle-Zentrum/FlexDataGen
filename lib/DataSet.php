<?php

require_once __DIR__ . '/Helper.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../generators/FlexDataGen.php';
foreach(glob(__DIR__ . '/../generators/*.php') as $file)
    require_once $file;
foreach(glob(__DIR__ . '/../distributions/*.php') as $file)
    require_once $file;

// ============================================================================
class DataSet {
/*
    Class that handles the processing of the tables settings file;
    Tables and columns are processed in the defined order.

    Construct a dataset object by providing the settings to the constructor
    and then invoking the generate() function.

    The generated data can be output using printArray() or printSql() functions
*/
// ============================================================================
    protected $tables;
    protected $cur;

    // ------------------------------------------------------------------------
    public function __construct(
        $tables
    ) {
    // ------------------------------------------------------------------------
        $this->tables = $tables;
        $this->cur = [];
    }

    // ------------------------------------------------------------------------
    public function generate(
    ) {
    // ------------------------------------------------------------------------
        $this->rowCounts = [];
        foreach($this->tables as $table_name => $table) {
            $this->curTableName = $table_name;
            if(is_callable($table)) {
                $this->cur[$table_name] = $table($this, $this->rowCounts[$table_name]);
                continue;
            }
            $this->rowCounts[$table_name] = $table['rows'];
            $this->cur[$table_name] = [];
            if(is_array($table['rows'])) { // table rows are constructed by combinations of columns
                $this->rowCounts[$table_name] = $this->generateCombinations($table['rows']);
            }
            else if(is_callable($table['rows'])) {
                $this->rowCounts[$table_name] = $table['rows']($this);
            }
            // now rows is a number that indicates the number of rows:
            foreach($table['columns'] as $column_name => $column) {
                // if anything else than an array, then take this literal for each row
                if(!is_array($column)) {
                    $this->cur[$table_name][$column_name] = array_fill(0, $this->rowCounts[$table_name], $column);
                }
                // ... else we have an array with generator or function settings
                else {
                    // let a generator do its work
                    if(isset($column['generator'])) {
                        $r = new ReflectionClass($column['generator']);
                        $column['options']['dataSet'] = $this;
                        $g = $r->newInstance($column['options']);
                        $this->cur[$table_name][$column_name] = $g->getData($this->rowCounts[$table_name]);
                    }
                    // otherwise invoke a user defined callback function to determine value row by row
                    else if(isset($column['callback'])) {
                        $curTableData = &$this->cur[$table_name];
                        $requiredColumns = isset($column['requiredColumns']) && count($column['requiredColumns']) > 0
                            ? $column['requiredColumns']
                            : array_keys($this->cur[$table_name]);
                        $colValues = [];
                        for($i = $this->rowCounts[$table_name] - 1; $i >= 0; $i--) {
                            $row = [];
                            foreach($requiredColumns as $requiredColumn) {
                                $row[$requiredColumn] = &$curTableData[$requiredColumn][$i];
                            }
                            $colValues[$i] = $column['callback']($row, $colValues, $this);
                        }
                        $curTableData[$column_name] = $colValues;
                    }
                    else
                        throw new Exception('Unknown column settings');
                }
            }

            // discard any columns?
            foreach($table['columns'] as $column_name => $column) {
                if(isset($column['discard']) && $column['discard'] === true) {
                    unset($this->cur[$table_name][$column_name]);
                }
            }
        }
    }

    // ------------------------------------------------------------------------
    public function getRowCount(
        $table_name
    ) {
    // ------------------------------------------------------------------------
        return $this->rowCounts[$table_name];
    }

    // ------------------------------------------------------------------------
    protected function addToCombination(
        $columnIndex,
        &$prevCombinations
    ) {
    // ------------------------------------------------------------------------
        if($columnIndex >= count($this->combinedColumnInfos)) {
            // now we have a full record in $prevCombinations
            for($i = 0; $i < count($prevCombinations); $i++) {
                $this->cur
                    [$this->curTableName]
                    [$this->combinedColumnInfos[$i]['field']]
                    [] = $prevCombinations[$i];
            }
            return;
        }

        $columnInfo = &$this->combinedColumnInfos[$columnIndex];
        
        if($columnIndex === 0) { // special treatment: pick by probability
            $pool = $this->getColumnValues(
                $columnInfo['options']['table'], 
                $columnInfo['options']['column']
            );
            foreach($pool as $value) {
                if(rand(0, 100) > 100. * $columnInfo['probability'])
                    continue;
                $prevCombinations = [ $value ];
                $this->addToCombination($columnIndex + 1, $prevCombinations);
            }
        }
        else {
            // number of combinations for this column
            $numRecords = $this->occurrenceDistributions[$columnIndex]->getRandomValue();
            $values = $this->columnGenerators[$columnIndex]->getData($numRecords);
            for($i = 0; $i < $numRecords; $i++) {
                $prevCombinations[$columnIndex] = $values[$i];
                $this->addToCombination($columnIndex + 1, $prevCombinations);
            }
        }
    }

    // ------------------------------------------------------------------------
    protected function generateCombinations(
        $columns
    ) {
    // ------------------------------------------------------------------------
        $numRows = 0;
        $this->combinedColumnInfos = $columns;
        $this->combinedValues = [];
        $this->columnGenerators = [];
        foreach($columns as $index => $column) {
            if($index > 0)
                $column['options']['dataSet'] = $this;
            $this->combinedValues[$column['field']] = [];
            $this->columnGenerators[$index] = (
                $index === 0 
                ? null // first column generated by fixed probability
                : Helper::getInstance($column['generator'], $column['options'])
            );
            $this->occurrenceDistributions[$index] = (
                $index === 0 
                ? null // first column generated by fixed probability
                : Helper::getInstanceArgs($column['combinations']['distribution'], [
                    'min' => $column['combinations']['min'],
                    'max' => $column['combinations']['max']
                ])
            );
        }
        
        $prevCombinations = [];
        $this->addToCombination(0, $prevCombinations);
        
        return count($this->cur[$this->curTableName][$columns[0]['field']]);
    }

    // ------------------------------------------------------------------------
    public function getColumnValues(
        $table_name, 
        $column_name
    ) {
    // ------------------------------------------------------------------------
        return $this->cur[$table_name][$column_name];
    }

    // ------------------------------------------------------------------------
    public function getTableRow(
        $table_name, 
        $row_key // e.g. [ 'id' => 35 ]
    ) {
    // ------------------------------------------------------------------------
        $data = &$this->cur[$table_name];
        for($i = $this->rowCounts[$table_name] - 1; $i >= 0; $i--) {
            $found = true;
            foreach($row_key as $col => $val) {
                if($data[$col][$i] !== $val) {
                    $found = false;
                    break;
                }
            }
            if($found) {
                $row = [];
                foreach($data as $column => $values)
                    $row[$column] = $values[$i];
                return $row;
            }
        }
        return false;
    }

    // ------------------------------------------------------------------------
    public function traverseTableRows(
        $table_name, 
        $callback
    ) {
    // ------------------------------------------------------------------------
        $data = &$this->cur[$table_name];
        $columns = array_keys($data);
        $rows = count($data[$columns[0]]);
        for($i = 0; $i < $rows; $i++) {
            $row = [];
            foreach($columns as $column)
                $row[$column] = $data[$column][$i];
            if(false === $callback($row))
                break;
        }
    }

    // ------------------------------------------------------------------------
    protected function getSqlLiteral(
        $value
    ) {
    // ------------------------------------------------------------------------
        if($value === null)
            return 'null';
        if(is_bool($value))
            return $value ? 'true' : 'false';
        if(is_string($value))
            return "'" . str_replace("'", "''", $value) . "'";
        if(is_array($value)) // take first array element literally
            return $value[0];
        return $value;
    }

    // ------------------------------------------------------------------------
    public function printArray(
    ) {
    // ------------------------------------------------------------------------
        print_r($this->cur);
    }
    
    // ------------------------------------------------------------------------
    public function printSql(
        $escapeChar = false
    ) {
    // ------------------------------------------------------------------------
        foreach($this->cur as $table_name => &$tableData) {
            echo sprintf(
                '%s-- %s: %s%s', 
                PHP_EOL, $table_name, $this->rowCounts[$table_name], PHP_EOL
            );
            $columnList = join(', ', array_map(function($v) use ($escapeChar) { 
                return $escapeChar === false ? $v : "$escapeChar$v$escapeChar";
            }, array_keys($tableData)));
            for($row = 0; $row < $this->rowCounts[$table_name]; $row++) {
                $values = [];
                foreach($tableData as $column_name => &$column) {
                    $values[] = $this->getSqlLiteral($column[$row]);
                }
                echo sprintf(
                    'insert into %s (%s) values (%s);',
                    $escapeChar === false ? $table_name : "$escapeChar$table_name$escapeChar",
                    $columnList,
                    join(', ', $values)
                ), PHP_EOL;
            }
        }
    }
}