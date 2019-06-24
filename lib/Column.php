<?php

// ============================================================================
class Column {
/*
    Helper class for generating column definitions more elegantly
*/
// ============================================================================

    // ------------------------------------------------------------------------
    protected static function makeOptions(
        $genOptions = [],
        $colOptions = [],
        $customOptions = []
    ) {
    // ------------------------------------------------------------------------
        foreach($customOptions as $opt => &$val) {
            if(in_array($opt, ['discard'])) // known column options
                $colOptions[$opt] = $val;
            else
                $genOptions[$opt] = $val;
        }
        return [$genOptions, $colOptions];
    }

    // ------------------------------------------------------------------------
    public static function callback(
        $func,
        $requiredColumns = [],
        $discard = false
    ) {
    // ------------------------------------------------------------------------
        return [
            'callback' => $func,
            'requiredColumns' => $requiredColumns,
            'discard' => $discard
        ];
    }

    // ------------------------------------------------------------------------
    public static function generator(
        $generator,
        $generatorOptions = [],
        $columnOptions = []
    ) {
    // ------------------------------------------------------------------------
        return array_merge_recursive([
            'generator' => $generator,
            'options' => $generatorOptions
        ], $columnOptions);
    }

    // ------------------------------------------------------------------------
    public static function category(
        $source,
        $distribution = 'UniformDistribution',
        $customOptions = []
    ) {
    // ------------------------------------------------------------------------
        list($genOptions, $colOptions) = self::makeOptions([
            'source' => $source,
            'distribution' => $distribution
        ], [], $customOptions);
        return self::generator('CategoryGenerator', $genOptions, $colOptions);
    }

    // ------------------------------------------------------------------------
    public static function foreignKey(
        $table,
        $column,
        $distribution = 'NormalDistribution',
        $customOptions = []
    ) {
    // ------------------------------------------------------------------------
        list($genOptions, $colOptions) = self::makeOptions([
            'table' => $table,
            'column' => $column,
            'distribution' => $distribution
        ], [], $customOptions);
        return self::generator('ForeignKeyGenerator', $genOptions, $colOptions);
    }

    // ------------------------------------------------------------------------
    public static function serial(
        $start = 1
    ) {
    // ------------------------------------------------------------------------
        return [
            'generator' => 'SerialGenerator',
            'options' => [
                'start' => $start
            ]
        ];
    }

    // ------------------------------------------------------------------------
    public static function integer(
        $min,
        $max,
        $distribution = 'UniformDistribution',
        $customOptions = []
    ) {
    // ------------------------------------------------------------------------
        return self::decimal($min, $max, 0, $distribution, $customOptions);
    }

    // ------------------------------------------------------------------------
    public static function decimal(
        $min,
        $max,
        $decimals,
        $distribution = 'UniformDistribution',
        $customOptions = []
    ) {
    // ------------------------------------------------------------------------
        list($genOptions, $colOptions) = self::makeOptions([
            'min' => $min,
            'max' => $max,
            'distribution' => $distribution,
            'decimals' => $decimals
        ], [], $customOptions);
        return self::generator('NumberGenerator', $genOptions, $colOptions);
    }

    // ------------------------------------------------------------------------
    public static function date(
        $min,
        $max,
        $distribution = 'UniformDistribution',
        $customOptions = []
    ) {
    // ------------------------------------------------------------------------
        list($genOptions, $colOptions) = self::makeOptions([
            'min' => $min,
            'max' => $max,
            'distribution' => $distribution
        ], [], $customOptions);
        return self::generator('DateGenerator', $genOptions, $colOptions);
    }

    // ------------------------------------------------------------------------
    public static function pattern(
        $pattern,
        $generators
    ) {
    // ------------------------------------------------------------------------
        return self::generator('PatternGenerator', [
            'pattern' => $pattern,
            'generators' => $generators
        ]);
    }
}