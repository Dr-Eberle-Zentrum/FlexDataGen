<?php

// ============================================================================
class Helper {
/*
    Helper convenience class 
*/
// ============================================================================
    
    // ------------------------------------------------------------------------
    public static function getInstance(
        $className, 
        $constructorArgs
    ) {
    // ------------------------------------------------------------------------
        $r = new ReflectionClass($className);
        return $r->newInstance($constructorArgs);
    }
    
    // ------------------------------------------------------------------------
    public static function getInstanceArgs(
        $className, 
        $constructorArgs
    ) {
    // ------------------------------------------------------------------------
        $r = new ReflectionClass($className);
        return $r->newInstanceArgs($constructorArgs);
    }

    // ------------------------------------------------------------------------
    public static function lineByLine(
        $fileName
    ) {
    // ------------------------------------------------------------------------
        $file = fopen($fileName, 'r');
        while(($line = fgets($file)) !== false)
            yield trim($line);
        fclose($file);
    }
}