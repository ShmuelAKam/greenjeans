<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

//=====================================================
// Debug-OUT.php - Some generic functions for debug output.

/**
 * Convert bool values to string.
 * Because print_r and echo don't show it's value properly.
 * TRUE has an extra space to enable tabulating outputs.
 * @param bool $value
 * @return string
 */
function boolToString(bool $value): string
{
    return $value ? "TRUE " : "FALSE";
}

/**
 * Convert enum value to string. Removing everything except the value.
 * @param  \UnitEnum $enumValue
 * @return string
 */
function enumToString(\UnitEnum $enumValue): string
{
    $fullString = print_r($enumValue, true);

    $valStart = strpos($fullString, 'value] => ') + 10;
    $val      = substr($fullString, $valStart);

    return $val;
}

/**
 * print_r TWO objects side by side, for comparison.
 * This function was written using Gemini.
 * @param mixed $obj1
 * @param mixed $obj2
 * @param int   $colWidth
 */
function print_rr(mixed $obj1, mixed $obj2, int $colWidth = 70): void
{
    // 1. Prepare Arrays
    $arr1   = (array) $obj1;
    $arr2   = (array) $obj2;
    $class1 = is_object($obj1) ? get_class($obj1) : '';
    $class2 = is_object($obj2) ? get_class($obj2) : '';

    // 2. Get unique keys from both
    $allKeys = array_unique(array_merge(array_keys($arr1), array_keys($arr2)));

    foreach ($allKeys as $key)
    {
        $has1 = array_key_exists($key, $arr1);
        $has2 = array_key_exists($key, $arr2);

        // 3. Clean Key Name (remove NULL bytes)
        $cleanKey = (string) $key;
        if (strpos($cleanKey, "\0") !== false)
        {
            $parts    = explode("\0", $cleanKey);
            $cleanKey = end($parts);
        }

        // 4. Capture Values into separate string variables
        $str1 = "";
        if ($has1)
        {
            $val = $arr1[$key];
            if ($val === true)
            {
                $str1 = "TRUE";
            }
            elseif ($val === false)
            {
                $str1 = "FALSE";
            }
            elseif ($val === null)
            {
                $str1 = "NULL";
            }
            else
            {
                $str1 = rtrim(print_r($val, true));
            }
        }

        $str2 = "";
        if ($has2)
        {
            $val = $arr2[$key];
            if ($val === true)
            {
                $str2 = "TRUE";
            }
            elseif ($val === false)
            {
                $str2 = "FALSE";
            }
            elseif ($val === null)
            {
                $str2 = "NULL";
            }
            else
            {
                $str2 = rtrim(print_r($val, true));
            }
        }

        // 5. Split into separate line arrays
        $lines1 = explode("\n", $str1);
        $lines2 = explode("\n", $str2);
        $max    = max(count($lines1), count($lines2));

        // 6. Output Side-by-Side
        for ($i = 0; $i < $max; $i++)
        {
            $leftSide = "";
            if ($has1)
            {
                $label    = ($i === 0) ? "[" . ($class1 ? "$class1:" : "") . "$cleanKey] => " : "";
                $leftSide = $label . ($lines1[$i] ?? '');
            }

            $rightSide = "";
            if ($has2)
            {
                $label     = ($i === 0) ? "[" . ($class2 ? "$class2:" : "") . "$cleanKey] => " : "";
                $rightSide = $label . ($lines2[$i] ?? '');
            }

            echo str_pad($leftSide, $colWidth, " ", STR_PAD_RIGHT) . " | " . $rightSide . PHP_EOL;
        }
    }
}

function getCaller(): string
{
    // Capture up to 3 frames to see past the constructor
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

    // If index 1 exists, we have a caller
    if (isset($trace[1]['function']))
    {
        // Is the immediate caller a constructor? Look deeper to index 2!
        if ($trace[1]['function'] === '__construct' && isset($trace[2]['function']))
        {
            $frame = $trace[2];
        }
        else
        {
            $frame = $trace[1];
        }

        $class = $frame['class'] ?? '';
        $type  = $frame['type'] ?? '';
        $func  = $frame['function'];

        return $class . $type . $func;
    }

    return 'Global Scope';
}
