<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\API_Base;

/**
 *
 * @author shmuel
 *         Class for parsing object ot get hidden values for tests.
 *         ALL methods are static.
 */
class Reflectrovitch
{
    /**
     * Return array member of object.
     * Works by parsing the output of print_r.
     *
     * @param API_Base $object
     * @param string   $arrayName Name of desired field.
     * @param int      $indentLevel
     * @return array<int|string, string> Requested array. Possibly empty [] (or
     *         NULL if nothing listed).
     */
    public static function getArrayFromObject(API_Base $object, string $arrayName, int $indentLevel = 1): ?array
    {
        $objectString = print_r($object, true);

        $arrayValues = self::getArrayFromString($objectString, $arrayName);

        return $arrayValues;
    }

    public static function getArrayField(API_Base $object, string $arrayName, string $fieldName, int $indentLevel = 1): ?string
    {
        $valuesArray = self::getArrayFromObject($object, $arrayName);

        if (array_key_exists($fieldName, $valuesArray))
        {
            $fieldValue = $valuesArray[$fieldName];

            return $fieldValue;
        }
        else
        {
            // Field name was NOT found.
            return null;
        }
    }

    /**
     * Return value of specific field in 'values' array that a number of classes have.
     *
     * @param API_Base $object
     *            Object to parse.
     * @param string $fieldName
     *            Name of desired field.
     * @return string Value of field.
     */
    public static function getValuesField(API_Base $object, string $fieldName): ?string
    {
        return self::getArrayField($object, 'values', $fieldName, 1);
    }

    /**
     * @param API_Base $object
     * @param string $fieldName
     * @return bool|NULL
     */
    public static function getValuesFieldBool(API_Base $object, string $fieldName): ?bool
    {
        $fieldValue = self::getValuesField($object, $fieldName);

        $boolValue = true;
        if ($fieldValue == '')
        {
            $boolValue = null;
        }

        return $boolValue;
    }

    /**
     * Get field IN 'values' that contains an array.
     *
     * @param API_Base $object
     * @param string $fieldName
     * @return array<string, string>|NULL
     */
    public static function getValuesFieldArray(API_Base $object, string $fieldName): ?array
    {
        $arrayString = self::getValuesField($object, $fieldName);

        $valuesArray = Reflectrovitch::makeArrayFromString($arrayString, 3);

        return $valuesArray;
    }

    /**
     * Return value of specific Event field in profiles->values array.
     *
     * @param API_Base $object
     * @param string   $fieldName Name of desired field.
     * @return mixed object created from field in larger object.
     */
    public static function getValuesFieldObj(API_Base $object, string $fieldName, string $className): mixed
    {
        $resultObject = null;

        // Isolate field from 'values'.
        $objectString = self::getValuesField($object, $fieldName);

        // Turn field string into an array.
        $objectArray = self::makeArrayFromString($objectString, 3, isObject: true);

        if (!empty($objectArray))
        {
            // Clean-up array by removing unneeded info from each KEY, and removing
            // keys for empty values.

            $cleanArray = [];

            foreach ($objectArray as $keyName => $value)
            {
                if ($value != '')
                {
                    $cleanKey = str_replace(":$className:private", "", $keyName);

                    $cleanArray[$cleanKey] = $value;
                }
            }

            if (count($cleanArray) > 0)
            {
                $resultObject = $className::newFromArray($cleanArray);
            }
        }

        return $resultObject;
    }

    // ------------------------------------------------------

    /**
     * @param string $objectString
     * @param string $arrayName
     * @param int $indentLevel
     * @return array<string, string>|NULL
     */
    protected static function getArrayFromString(string $objectString, ?string $arrayName, int $indentLevel = 1): ?array
    {
        // Isolate the requested array.
        $headerArrayName = "\n" . str_repeat(' ', $indentLevel * 4) . "[$arrayName:";
        $headerPos       = strpos($objectString, $headerArrayName);

        if ($headerPos === false)
        {
            $headerArrayName = "\n" . str_repeat(' ', $indentLevel * 4) . "[$arrayName]";
            $headerPos       = strpos($objectString, $headerArrayName);
        }

        $arrayString = substr($objectString, $headerPos);
        $arrayString = substr($arrayString, strpos($arrayString, "] => ") + 5);

        $arrayValues = self::makeArrayFromString($arrayString, $indentLevel);

        return $arrayValues;
    }

    /**
     * @param string $arrayString
     * @param int    $indentLevel
     * @param bool   $isObject
     * @return array<string, string>|NULL
     */
    protected static function makeArrayFromString(string $arrayString, int $indentLevel, bool $isObject = false): ?array
    {
        $arrayValues = null;

        // Check if has array value (could be NULL, i.e. nothing on rest of line).

        if (strpos($arrayString, 'Array') === 0 || $isObject)
        {
            // Remove the lines with the 'Array', '(' & ')', leaving only the array values.

            $headerParentheses = "\n" . str_repeat(' ', ($indentLevel + 1) * 4);

            $arrayStart = strpos($arrayString, $headerParentheses . '(') + 10; // position of \n EOL.
            $arrayEnd   = strpos($arrayString, $headerParentheses . ')'); // EOL of last line.

            $arrayString = substr($arrayString, $arrayStart, $arrayEnd - $arrayStart);

            $lineBeginning = "\n" . str_repeat(' ', ($indentLevel + 2) * 4) . '[';

            $arrayValues = [];

            if (strlen($arrayString) > 0)
            {
                $arrayLines = explode($lineBeginning, $arrayString);

                // Convert each line into key => value pairs.

                foreach ($arrayLines as $line)
                {
                    $keyEnd = strpos($line, ']');

                    $key = substr($line, 0, $keyEnd);
                    if ($indentLevel == 1)
                    {
                        $value = substr($line, $keyEnd + 5);
                    }
                    else
                    {
                        $value = trim(substr($line, $keyEnd + 5));
                    }

                    if ($key != '')
                    {
                        $arrayValues[$key] = $value;
                    }
                }
            }
        }

        return $arrayValues;
    }

    /**
     * @param string $arrayString
     * @param string $compoundName
     * @param int    $indentLevel
     * @return array<string, string>|NULL
     */
    protected static function makeCompoundFromString(string $arrayString, string $compoundName, int $indentLevel): ?array
    {
        $arrayValues = null;

        // Check if has array value (could be NULL, i.e. nothing on rest of line).

        if (strpos($arrayString, 'Array') === 0)
        {
            // Remove the lines with the 'Array', '(' & ')', leaving only the array values.

            $headerParentheses = "\n" . str_repeat(' ', ($indentLevel + 1) * 4);

            $arrayStart = strpos($arrayString, $headerParentheses . '(') + 10; // position of \n EOL.
            $arrayEnd   = strpos($arrayString, $headerParentheses . ')') + 2;  // position of ' ' at beginning of line.

            $arrayString = substr($arrayString, $arrayStart, $arrayEnd - $arrayStart);

            $lineBeginning = "\n" . str_repeat(' ', ($indentLevel + 2) * 4) . '[';

            $arrayValues = [];

            if (strlen($arrayString) > 0)
            {
                $arrayLines = explode($lineBeginning, $arrayString);

                // Convert each line into key => value pairs.

                foreach ($arrayLines as $line)
                {
                    $keyEnd = strpos($line, ']');

                    $key = substr($line, 0, $keyEnd); // Offset 1 for '['.
                    if ($indentLevel == 1)
                    {
                        $value = substr($line, $keyEnd + 5, - 2);
                    }
                    else
                    {
                        $value = trim(substr($line, $keyEnd + 5));
                    }

                    if ($key != '')
                    {
                        $arrayValues[$key] = $value;
                    }
                }
            }
        }

        return $arrayValues;
    }
}
