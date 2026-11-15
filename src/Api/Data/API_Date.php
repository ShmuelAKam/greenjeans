<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

use DateTimeImmutable;
use DateTimeZone;
use ShmuelAKam\Greenjeans\Api\Core\API_Exception;

/**
 * API_Date - Used by media objects, and API_Event.
 *
 * This object is implemented internally as an ARRAY, because
 * ALL of the fields are optional, and it is just as important to know
 * what fields are NOT set.
 *
 * This basic data structure does NOT have an API of its own.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=date API_Date}
 */
class API_Date
{
    /**
     * List of valid field names, for hydrating object from server
     * (or persistance layer).
     * @see API_Date::updateFromArray
     * @var array <string, TRUE>
     */
    public const VALID_FIELDS = [
        'circa'     =>  true, // Indicates that the date is an approximation.
        'day'       =>  true, // Date's day
        'month'     =>  true, // month
        'year'      =>  true, // year.
        'end_circa' =>  true, // Indicates that the end date is an approximation.
        'end_day'   =>  true, // Date's end day (only valid if range is 'between').
        'end_month' =>  true, // end month.
        'end_year'  =>  true, // end year.
        'range'     =>  true, // Range ('before', 'after' or 'between').
    ];

    public const VALID_RANGE_VALUES = ['before', 'after', 'between'];

    /**
     * Number of days in each month.
     * This table is used ONLY when the year is NOT given. Since we can't know
     * If it's a leap-year or not, the table uses 29 for February, to cover all
     * possible valid values.
     * @var array<int, int>
     */
    public const DAYS_IN_MONTH = [
         1 => 31, // January
         2 => 29, // February (See comment above about leap years).
         3 => 31, // March
         4 => 30, // April
         5 => 31, // May
         6 => 30, // June
         7 => 31, // July
         8 => 31, // August
         9 => 30, // September
        10 => 31, // October
        11 => 30, // November
        12 => 31  // December
    ];

    //------------------------------------------------------

    /**
     * Array of date field values.
     * ALL fields in this object are <b>optional.</b> So an array is used to hold
     * only those defined. This makes it easiest to see what is defined.
     * @see API_Date::VALID_FIELDS.
     *
     * @var array<string, mixed>
     */
    private array $values = [];

    //------------------------------------------------------

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->values = [];
    }

    /**
     * Check if both objects have the exact same fields.
     * @param API_Date $first
     * @param API_Date $second
     * @return bool
     */
    public static function __equals(?API_Date $first, ?API_Date $second): bool
    {
        $same = false;

        if ($first != null && $second != null)
        {
            if (count($first->values) == count($second->values))
            {
                // Same number of fields. Compare the fields.
                $diffs = array_diff_assoc($first->values, $second->values);
                if (count($diffs) == 0)
                {
                    $same = true;
                }
            }
        }
        else
        {
            if ($first == null && $second == null)
            {
                $same = true;
            }
        }

        return $same;
    }

    /**
     * Update current object with values returned from the Server. It does NOT
     * validate the data fields (except for 'range'), because that potentially
     * could reject already existing data.
     *
     * NOTE: Unlike other updateFromArray functions, this does a FULL reset of
     * the object before updating it. There is NO point in mixing existing
     * values with new ones. There are NO default values.
     *
     * @param array<string, int|string> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        $this->reset(); // Clear ALL fields before assiging ANY values.

        foreach ($arrayValues as $fieldname => $field)
        {
            if (isset(self::VALID_FIELDS [$fieldname]))
            {
                $this->values [$fieldname] = $field;
            }
        }

        // Verify that range value is valid. It has already been set by
        // the loop above. So if invalid, we must remove it.

        if (isset($arrayValues ['range'])
            && !in_array($arrayValues ['range'], self::VALID_RANGE_VALUES))
        {
            unset($this->values ['range']);
        }
    }

    /**
     * Factory function.
     * Creates object from values sent by Server (or the persistence layer).
     * @param array<string, int|string> $arrayValues
     * @return API_Date
     */
    public static function newFromArray(array $arrayValues): API_Date
    {
        $date = new API_Date();

        $date->updateFromArray($arrayValues);

        return $date;
    }

    /**
     * Returns array to send be to Server in API calls (as part of larger objects).
     * @return array<string, int|string>
     */
    public function makeParamArray(): array
    {
        return $this->values;
    }

    //------------------------------------------------------
    // Converter functions.

    /**
     * Convert this object to Epoch time.
     * @return int
     */
    public function toEpoch(): int
    {
        if (!isset($this->values ['year']))
        {
            throw new API_Exception("Can't convert to epoch. No year", API_Exception::BAD_DATE);
        }

        $year  = $this->values['year'];
        $month = $this->values['month'] ?? 1;
        $day   = $this->values['day'] ?? 1;

        // Must call setTime(0, 0, 0) FIRST so setDate() operates starting at midnight UTC
        $date = (new DateTimeImmutable('today', new DateTimeZone('UTC')))
            ->setTime(0, 0, 0)
            ->setDate($year, $month, $day);

        return $date->getTimestamp();
    }

    /**
     * Convert this object to a timestamp. Using format d/m/y.
     * If it's a range then format is: [range type] d/m/y - d/m/y.
     * @return string
     */
    public function toTimestamp(): string
    {
        $circa     = $this->values ['circa'] ?? null;
        $day       = $this->values ['day'] ?? null;
        $month     = $this->values ['month'] ?? null;
        $year      = $this->values ['year'] ?? null;
        $end_circa = $this->values ['end_circa'] ?? null;
        $end_day   = $this->values ['end_day'] ?? null;
        $end_month = $this->values ['end_month'] ?? null;
        $end_year  = $this->values ['end_year'] ?? null;
        $range     = $this->values ['range'] ?? null;

        $start = $this->makeTimestamp($day, $month, $year, $circa);
        $end   = $this->makeTimestamp($end_day, $end_month, $end_year, $end_circa);

        $timestamp = '';

        if ($start != '')
        {
            $timestamp = $start;
        }

        if ($end != '')
        {
            $timestamp .= "-$end";
        }

        if (isset($this->values ['range']))
        {
            $timestamp = "$range $timestamp";
        }

        return $timestamp;
    }

    /**
     * Convert this object to time in JSON format (ISO 8601): YYYY-MM-DD
     * TODO Should I somehow return the end-time if it exists?
     * @return string
     */
    public function toJSON(): string
    {
        if (!isset($this->values['year']))
        {
            return '0';
        }

        $year = $this->values['year'];

        // Negative years require 6 digits with leading minus sign (e.g., -000044)
        // Positive years require at least 4 digits (e.g., 0070 or 1979)
        $dateStr = $year < 0
            ? sprintf('-%06d', abs($year))
            : sprintf('%04d', $year);

        if (isset($this->values['month']))
        {
            $dateStr .= sprintf('-%02d', $this->values['month']);

            if (isset($this->values['day']))
            {
                $dateStr .= sprintf('-%02d', $this->values['day']);
            }
        }

        return $dateStr;
    }

    //------------------------------------------------------
    // Static creator functions.

    /**
     * Use epoch time to create API_Date object.
     * Convert to string and back, in order to remove time part.
     * @param int $epochTime
     * @return API_Date
     */
    public static function fromEpoch(?int $epochTime = null): API_Date
    {
        if ($epochTime === null)
        {
            $epochTime = time();
        }

        // Initialize in UTC and set the timestamp directly
        $dateTime = (new \DateTimeImmutable('@' . $epochTime))
            ->setTimezone(new \DateTimeZone('UTC'));

        $year  = (int) $dateTime->format('Y');
        $month = (int) $dateTime->format('m');
        $day   = (int) $dateTime->format('d');

        // Create and return the API_Date instance

        return API_Date::newFromArray([
            'year'  => $year,
            'month' => $month,
            'day'   => $day,
        ]);
    }

    /**
     * Use timestamp (d/m/y format) to create API_Date object.
     * @param string $startTimestamp
     * @param string $endTimestamp   Optional.
     * @return API_Date
     */
    public static function fromTimestamp(string $startTimestamp, string $endTimestamp = ''): API_Date
    {
        $dateFields = [ 'day', 'month', 'year', 'end_day', 'end_month', 'end_year' ];

        $startDateVals = explode('/', $startTimestamp);

        foreach ($startDateVals as $index => $field)
        {
            $arrayValues [$dateFields [$index]] = (int) trim($field);
        }

        if ($endTimestamp != '')
        {
            $endDateVals = explode('/', $endTimestamp);

            foreach ($endDateVals as $index => $field)
            {
                $arrayValues [$dateFields [$index + 3]] = (int) trim($field);
            }
        }

        $date = API_Date::newFromArray($arrayValues);

        return $date;
    }

    /**
     * Use JSON encoded time to create API_Date object.
     * @param string $startJSON
     * @param string $endJSON   Optional.
     * @return API_Date
     */
    public static function fromJSON(string $startJSON, string $endJSON = ''): API_Date
    {
        $arrayValues = [];

        self::parseJSONDate($startJSON, 'year', 'month', 'day', $arrayValues);

        if ($endJSON !== '')
        {
            self::parseJSONDate($endJSON, 'end_year', 'end_month', 'end_day', $arrayValues);
        }

        return API_Date::newFromArray($arrayValues);
    }

    /**
     * Create API_Date object for current date.
     * @return API_Date
     */
    public static function today(): API_Date
    {
        return API_Date::fromEpoch();
    }

    //------------------------------------------------------

    /**
     * Get date.
     * @return array<string ,int|bool>
     */
    public function getDate(): array
    {
        $date = [];

        if (isset($this->values ['day'  ]))
        {
            $date ['day'   ] = $this->values ['day'];
        }

        if (isset($this->values ['month']))
        {
            $date ['month' ] = $this->values ['month'];
        }

        if (isset($this->values ['year' ]))
        {
            $date ['year'  ] = $this->values ['year'];
        }

        if (isset($this->values ['circa']))
        {
            $date ['circa' ] = $this->values ['circa'];
        }

        return $date;
    }

    /**
     * Get end date (if range).
     * @return array<string ,int|bool>
     */
    public function getEndDate(): array
    {
        $endDate = [];

        if (isset($this->values ['end_day'  ]))
        {
            $endDate ['end_day'   ] = $this->values ['end_day'];
        }

        if (isset($this->values ['end_month']))
        {
            $endDate ['end_month' ] = $this->values ['end_month'];
        }

        if (isset($this->values ['end_year' ]))
        {
            $endDate ['end_year'  ] = $this->values ['end_year'];
        }

        if (isset($this->values ['end_circa']))
        {
            $endDate ['end_circa' ] = $this->values ['end_circa'];
        }

        return $endDate;
    }

    /**
     * Returns TYPE of range (or empty string if not).
     * @return string one of 'before', 'after', 'between' (OR '' when not range).
     */
    public function getRange(): string
    {
        return $this->values ['range'] ?? '';
    }

    //------------------------------------------------------

    /**
     * Set Date.
     *
     * This function sets ONLY the fields passed to it. So previous values in
     * other fields remain. If you do not want them, first call API_Date::reset.
     * @param int $day
     * @param int $month
     * @param int $year
     * @param bool   $circa
     */
    public function setDate(
        ?int  $day = null,
        ?int  $month = null,
        ?int  $year = null,
        ?bool $circa = null,
    ): void {
        if ($this->isDateValid($day, $month, $year))
        {
            if ($day != null)
            {
                $this->values ['day'] = $day;
            }

            if ($month != null)
            {
                $this->values ['month'] = $month;
            }

            if ($year != null)
            {
                $this->values ['year'  ] = $year;
            }

            if ($circa != null)
            {
                $this->values ['circa' ] = $circa;
            }
        }

        // If date is NOT valid, then an exception was thrown by isDateValid.
    }

    /**
     * Set end-date (when it's a range).
     *
     * This function sets ONLY the fields passed to it. So previous values in
     * other fields remain. If you do not want them, first call API_Date::reset.
     * NOTE: This function does NOT automatically add a 'range' field. This must
     * be done using the setRange function.
     * @param int  $end_day
     * @param int  $end_month
     * @param int  $end_year
     * @param bool $end_circa
     */
    public function setEndDate(
        ?int  $end_day = null,
        ?int  $end_month = null,
        ?int  $end_year = null,
        ?bool $end_circa = null,
    ): void {
        if ($this->isDateValid($end_day, $end_month, $end_year))
        {
            if ($end_day != null)
            {
                $this->values ['end_day'  ] = $end_day;
            }

            if ($end_month != null)
            {
                $this->values ['end_month'] = $end_month;
            }

            if ($end_year != null)
            {
                $this->values ['end_year' ] = $end_year;
            }

            if ($end_circa != null)
            {
                $this->values ['end_circa'] = $end_circa;
            }
        }

        // If date is NOT valid, then an exception was thrown by isDateValid.
    }

    /**
     * Set field indicating this date is a range (before, after, or between).
     * TODO Should I verify that both dates are set when setting range to 'between'?
     * @param string $range
     */
    public function setRange(?string $range): void
    {
        if ($range != null && in_array($range, self::VALID_RANGE_VALUES))
        {
            $this->values ['range'] = $range;
        }
    }

    /**
     * Reset ALL fields.
     * This is done by removing the entire $values array and creating a new one.
     */
    public function reset(): void
    {
        unset($this->values);

        $this->values = [];
    }

    //------------------------------------------------------
    // Single field getters. If field not defined, return NULL,
    // or FALSE if bool field.

    public function getDay(): ?int
    {
        return $this->values ['day'] ?? null;
    }

    public function getMonth(): ?int
    {
        return $this->values ['month'] ?? null;
    }

    public function getYear(): ?int
    {
        return $this->values ['year'] ?? null;
    }

    public function isCirca(): bool
    {
        return $this->values ['circa'] ?? false;
    }

    public function getEndDay(): ?int
    {
        return $this->values ['end_day'] ?? null;
    }

    public function getEndMonth(): ?int
    {
        return $this->values ['end_month'] ?? null;
    }

    public function getEndYear(): ?int
    {
        return $this->values ['end_year'] ?? null;
    }

    public function isEndCirca(): bool
    {
        return $this->values ['end_circa'] ?? false;
    }

    //------------------------------------------------------

    /**
     * Generates a normalized 'YYYY-MM-DD' string representation suitable for
     * chronological sorting and comparisons.
     *
     * @return string Normalized 8-character sort string (e.g., '17320222').
     */
    public function getSortKey(): string
    {
        $year  = isset($this->values ['year']) ? sprintf('%04d', $this->values ['year']) : '9999';
        $month = isset($this->values ['month']) ? sprintf('%02d', $this->values ['month']) : '00';
        $day   = isset($this->values ['day']) ? sprintf('%02d', $this->values ['day']) : '00';

        return "$year-$month-$day";
    }

    /**
     * Create string for ONE of the two dates. Uses format d/m/y.
     * @param int $day
     * @param int $month
     * @param int $year
     * @return string
     */
    private function makeTimestamp(?int $day = null, ?int $month = null, ?int $year = null, ?bool $circa = false): string
    {
        $dateString = "";

        if ($circa)
        {
            $dateString .= "~";
        }

        if ($day != null)
        {
            $dateString .= "$day/";
        }

        if ($month != null)
        {
            $dateString .= "$month/";
        }

        if ($year != null)
        {
            $dateString .= $year;
        }

        // If string ends with '/', then remove it.
        if (str_ends_with($dateString, '/'))
        {
            $dateString = substr($dateString, strlen($dateString) - 1);
        }

        return $dateString;
    }

    /**
     * Parse single JSON date into $arrayValues fields.
     * @param string $jsonDate
     * @param string $yearKey   Name of date  field. 'day'   or 'end_day'.
     * @param string $monthKey  Name of month field. 'month' or 'end_month'.
     * @param string $dayKey    Name of year  field. 'year'  or 'end_year'.
     * @param array <string, int> $arrayValues Passed by reference.
     */
    private static function parseJSONDate(string $jsonDate, string $yearKey, string $monthKey, string $dayKey, array &$arrayValues): void
    {
        $matches = [];

        if (preg_match('/^(?P<year>-?\d+)(?:-(?P<month>\d{2}))?(?:-(?P<day>\d{2}))?$/', $jsonDate, $matches))
        {
            $arrayValues[$yearKey] = (int) $matches['year'];

            if (isset($matches['month']))
            {
                $arrayValues[$monthKey] = (int) $matches['month'];
            }

            if (isset($matches['day']))
            {
                $arrayValues[$dayKey] = (int) $matches['day'];
            }
        }
        else
        {
            throw new API_Exception("Invalid JSON date format: {$jsonDate}", API_Exception::BAD_DATE);
        }
    }

    /**
     * Validate date parameters.
     * @param int $day
     * @param int $month
     * @param int $year
     * @return bool TRUE when date valid.
     * @throws API_Exception when numbers given are not valid.
     */
    private function isDateValid(?int $day = null, ?int $month = null, ?int $year = null): bool
    {
        if (!$this->daysOK($day, $month, $year))
        {
            throw new API_Exception("Num of days ($day) not valid ($month/$year)", API_Exception::BAD_DATE);
        }

        if (!$this->monthOK($month))
        {
            throw new API_Exception('Month not valid', API_Exception::BAD_DATE);
        }

        if (!$this->yearOK($year))
        {
            throw new API_Exception('Future year given', API_Exception::BAD_DATE);
        }

        return true;
    }

    /**
     * Validate number of days, according to month (and optional year).
     * Function must receive BOTH day & month to be valid.
     * NULL values for parameters must be accounted for, because this
     * can be set in previous functions.
     * @param  int $day
     * @param  int $month
     * @param  int $year
     * @return bool
     */
    private function daysOK(?int $day = null, ?int $month = null, ?int $year = null): bool
    {
        if ($day == null)
        {
            return true;
        }

        // Have day value.
        if ($month == null)
        {
            // Can't have a date with days, but no month.
            return false;
        }

        // Get number of days in the month.
        if ($year == null)
        {
            // Year not given. This is a broken date (but valid). Use
            // internal look-up table. Can NOT check if February is a
            // leap-year.

            $daysInMonth = self::DAYS_IN_MONTH [$month];
        }
        else
        {
            // Year given. Use PHP native function.
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        }

        if ($day <= $daysInMonth)
        {
            return true;
        }

        return false;
    }

    /**
     * Validates month value.
     * @param int $month
     * @return bool
     */
    private function monthOK(?int $month): bool
    {
        if ($month != null)
        {
            if ($month >= 1 && $month <= 12)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        // Month is NULL. This is OK.
        return true;
    }

    /**
     * Validates month value.
     * @param int $year
     * @return bool
     */
    private function yearOK(?int $year): bool
    {
        if ($year != null)
        {
            // Verify year is not in the future.
            $currentYear = (int) date('Y');

            if ($year > $currentYear)
            {
                return false;
            }
        }

        // Year is NULL. This is OK (broken date).
        return true;
    }
}
