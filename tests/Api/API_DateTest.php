<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDates;

/**
 * API_Date test case.
 * The API_Date class is a basic data-structure. It does NOT have
 * any API of it's own. So the server is NOT needed to test it.
 * TODO this class is NOT implemented!
 */
class API_DateTest extends API_BaseTest
{
    /** @var API_Date */
    private ?API_Date $aPI_Date;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->aPI_Date = new API_Date();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        // TODO Auto-generated API_DateTest::tearDown()
        $this->aPI_Date = null;

        parent::tearDown();
    }

    // =====================================================
    // Test providers.

    /**
     * Provide arrays of random day, month and year values.
     * @return array <array <int>>
     */
    public static function provideDateParams(): array
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        static $NUM_DATES = 10;

        $dates = [];

        for ($ind = 0; $ind < $NUM_DATES; $ind++)
        {
            $year  = $dateFactory->makeRandomYear();
            $month = $dateFactory->makeRandomMonth();
            $day   = $dateFactory->makeRandomDay($month, $year);

            $key = sprintf('%02d-%02d-%04d', $day, $month, $year);

            $dates [$key] = [ $day, $month, $year ];
        }

        // Add flag for 'circa' values.

        $dates = self::flagify($dates, 1);

        return $dates;
    }

    /**
     * Provide arrays of two sets of random day, month and year values.
     * @return array <array <int|bool>>
     */
    public static function provideFullDateParams(): array
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        static $NUM_DATES = 10;

        $dates = [];

        for ($ind = 0; $ind < $NUM_DATES; $ind++)
        {
            $year  = $dateFactory->makeRandomYear();
            $month = $dateFactory->makeRandomMonth();
            $day   = $dateFactory->makeRandomDay($month, $year);
            $circa = $dateFactory->makeRandomCirca();

            $otherYear = $dateFactory->makeRandomYear();
            $otherYear = ($year <= $otherYear) ? $otherYear : $year + 1;

            $otherMonth = $dateFactory->makeRandomMonth();
            $otherDay   = $dateFactory->makeRandomDay($otherMonth, $otherYear);
            $otherCirca = $dateFactory->makeRandomCirca();

            $key = sprintf(
                '%02d-%02d-%04d %d - %02d-%02d-%04d %d',
                $day,
                $month,
                $year,
                $circa,
                $otherDay,
                $otherMonth,
                $otherYear,
                $otherCirca,
            );

            $dates [$key] = [ $day, $month, $year, $circa, $otherDay, $otherMonth, $otherYear, $otherCirca ];
        }

        return $dates;
    }

    /**
     * Provide arrays of TWO sets of random day, month and year values,
     * that are different from each other. The data-set tests checks
     * every combination of changes in date, month and year.
     * @return array <array <int>>
     */
    public static function provideNotEqualParams(): array
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $baseDates    = self::provideDateParams();
        $flaggedDates = self::flagify($baseDates, 3);

        // Remove the last entry. The three flags will be F-F-F. This will
        // result in creating an identical date, which will fail the 'not
        // same' test.
        array_pop($flaggedDates);

        $dates = [];

        foreach ($flaggedDates as $date)
        {
            list($day, $month, $year, /*$circa*/, $changeDay, $changeMonth, $changeYear) = $date;

            $failCounter = 0;
            do
            {
                $otherYear  = $changeYear ? $dateFactory->makeRandomYear() : $year;
                $otherMonth = $changeMonth ? $dateFactory->makeRandomMonth() : $month;
                $otherDay   = $dateFactory->makeRandomDay($otherMonth, $otherYear);

                if ($year != $otherYear || $month != $otherMonth || $day != $otherDay)
                {
                    $flags = ($changeDay ? 'T' : 'F') . '-'
                        . ($changeMonth ? 'T' : 'F') . '-'
                        . ($changeYear ? 'T' : 'F');

                    $key = sprintf('%02d-%02d-%04d != %02d-%02d-%04d %s', $day, $month, $year, $otherDay, $otherMonth, $otherYear, $flags);

                    $dates [$key] = [ $day, $month, $year, $otherDay, $otherMonth, $otherYear ];
                    break;
                }
                else
                {
                    $failCounter++;
                }
            }
            while ($failCounter < 3);
        }

        return $dates;
    }

    /**
     * Provide arrays of random day, month and year values.
     * @return array <array <int>>
     */
    public static function provideDateParamsBadFields(): array
    {
        static $BAD_FIELD_NAMES = [
            'ZIPCODE',
            'd date',
            'Beebly',
            'BOOP',
            'Zipideedoo',
            'Armageddon date'
        ];

        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $dates = [];

        foreach ($BAD_FIELD_NAMES as $fieldName)
        {
            $year  = $dateFactory->makeRandomYear();
            $month = $dateFactory->makeRandomMonth();
            $day   = $dateFactory->makeRandomDay($month, $year);

            $key = sprintf('%02d-%02d-%04d', $day, $month, $year);

            $dates [$key] = [ $day, $month, $year, $fieldName ];
        }

        return $dates;
    }

    /**
     * @return array <string, array <int|bool|string>>
     */
    public static function provideDateWithRange(): array
    {
        $ranges = API_Date::VALID_RANGE_VALUES;

        // Remove 'between' because it's a special case.
        $key = array_search('between', $ranges);
        unset($ranges [$key]);

        $dates      = self::provideDateParams();
        $rangeDates = [];

        foreach ($dates as $key => $value)
        {
            foreach ($ranges as $range)
            {
                $rangeKey = "$key $range";

                $rangeDates [$rangeKey] = [...$value, $range];
            }
        }

        return $rangeDates;
    }

    /**
     * Provide array of different formats for famous dates.
     * @return array <string, array <int|string>>
     */
    public static function provideDateFormats(): array
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        return $dateFactory::DATE_FORMATS;
    }

    /**
     * Provide array of range of holiday dates in timestamp and JSON formats.
     * Parameter list when used:
     * array $start, array $end, string $startTimestamp, string $endTimestamp, string $startJSON, string $endJSON,
     * @return array <string, array <array <string>|string>>
     */
    public static function provideDateRangeFormats(): array
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        return $dateFactory::DATE_RANGE_FORMATS;
    }

    // =====================================================
    // Helper functions.

    /**
     * @param bool $start
     * @param bool $end
     * @param int  $day
     * @param int  $month
     * @param int  $year
     * @param bool $circa
     * @param int ...$endDateVals
     * @return array <string, int|bool>
     */
    protected static function makeDateArray(
        bool $start,
        bool $end,
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int ... $endDateVals,
    ): array {
        $startArray = [];

        if ($start)
        {
            $startArray = [
                'day'       => $day,
                'month'     => $month,
                'year'      => $year
            ];

            if ($circa)
            {
                $startArray ['circa'] = true;
            }

        }

        $endArray = [];

        if ($end)
        {
            list($endDay, $endMonth, $endYear, $endCirca) = $endDateVals;

            $endArray = [
                'end_day'   => $endDay,
                'end_month' => $endMonth,
                'end_year'  => $endYear,
                ];

            if ((bool) $endCirca)
            {
                $endArray ['end_circa'] = true;
            }
        }

        $dateArray = array_merge($startArray, $endArray);

        return $dateArray;
    }

    /**
     * Make date from start and end arrays.
     * @param array  <int> $start
     * @param array  <int> $end
     * @return API_Date
     */
    protected static function makeFullDate(array  $start, array $end): API_Date
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $day       = $start [0];
        $month     = $start [1];
        $year      = $start [2];
        $end_day   = $end   [0];
        $end_month = $end   [1];
        $end_year  = $end   [2];

        $date = $dateFactory->makeFullDate(
            $day,
            $month,
            $year, /*$circa:*/
            false,
            $end_day,
            $end_month,
            $end_year,
            end_circa: false,
        );

        return $date;
    }

    // =====================================================
    // Custom assert functions.

    protected static function assertDate(API_Date $date, bool $full, int $day, int $month, int  $year, bool $circa, int ... $dateVals): void
    {
        if ($full)
        {
            list($endDay, $endMonth, $endYear, $endCirca) = $dateVals;

            $endCirca = (bool) $endCirca;
        }

        self::assertEquals($date->getDay(), $day);
        self::assertEquals($date->getMonth(), $month);
        self::assertEquals($date->getYear(), $year);
        self::assertEquals($date->isCirca(), $circa);

        if ($full)
        {
            self::assertEquals($date->getEndDay(), $endDay);
            self::assertEquals($date->getEndMonth(), $endMonth);
            self::assertEquals($date->getEndYear(), $endYear);
            self::assertEquals($date->isEndCirca(), $endCirca);
        }
    }

    protected static function assertDateX(API_Date $date, bool $full, int ... $dateVals): void
    {
        if (!$full)
        {
            list($day, $month, $year, $circa) = $dateVals;

            $circa = (bool) $circa;
        }
        else
        {
            list($day, $month, $year, $circa, $endDay, $endMonth, $endYear, $endCirca) = $dateVals;

            $circa    = (bool) $circa;
            $endCirca = (bool) $endCirca;
        }

        self::assertEquals($date->getDay(), $day);
        self::assertEquals($date->getMonth(), $month);
        self::assertEquals($date->getYear(), $year);
        self::assertEquals($date->isCirca(), $circa);

        if ($full)
        {
            self::assertEquals($date->getEndDay(), $endDay);
            self::assertEquals($date->getEndMonth(), $endMonth);
            self::assertEquals($date->getEndYear(), $endYear);
            self::assertEquals($date->isEndCirca(), $endCirca);
        }
    }

    protected static function assertDateField(
        string $fieldname,
        int    $day,
        int    $month,
        int    $year,
        bool   $circa,
        int    $endDay,
        int    $endMonth,
        int    $endYear,
        bool   $endCirca,
    ): void {
        $dateArray = self::makeDateArray(true, true, $day, $month, $year, $circa, $endDay, $endMonth, $endYear, (int) $endCirca);
        $date      = API_Date::newFromArray($dateArray);

        if ($fieldname != 'circa' && $fieldname != 'end_circa')
        {
            $getter = self::snakeToCamel('get_' . $fieldname);

            // --------------------------------------------------
            $fieldValue = $date->$getter();
            // --------------------------------------------------

            self::assertEquals($dateArray [$fieldname], $fieldValue);
        }
        else
        {
            // $fieldname is 'circa' or 'end_circa'

            // Bool values are only set in the object array if TRUE.

            $getter = self::snakeToCamel('is_' . $fieldname);

            // --------------------------------------------------
            $fieldValue = $date->$getter();
            // --------------------------------------------------

            if (isset($dateArray [$fieldname]))
            {
                $value = $dateArray [$fieldname];
            }
            else
            {
                $value = false;
            }

            self::assertEquals($value, $fieldValue);
        }
    }

    // =====================================================
    // Test functions.

    /**
     * Tests API_Date->__construct()
     */
    public function test__construct(): void
    {
        // --------------------------------------------------
        $this->aPI_Date->__construct();
        // --------------------------------------------------

        $dateArray = $this->aPI_Date->makeParamArray();

        self::assertEmpty($dateArray);
    }

    /**
     * Tests API_Date::__equals()
     * @dataProvider provideDateParams
     */
    public function test__equals(int $day, int $month, int  $year, bool $circa): void
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $date     = $dateFactory->makeDate($day, $month, $year, $circa);
        $sameDate = $dateFactory->makeDate($day, $month, $year, $circa);

        // --------------------------------------------------
        $same = API_Date::__equals($date, $sameDate);
        // --------------------------------------------------

        self::assertTrue($same, 'dates are NOT the same');
    }

    /**
     * Tests API_Date::__equals()
     * @dataProvider provideNotEqualParams
     */
    public function test__NOTequals(int $day, int $month, int  $year, int $otherDay, int $otherMonth, int $otherYear): void
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $date     = $dateFactory->makeDate($day, $month, $year);
        $diffDate = $dateFactory->makeDate($otherDay, $otherMonth, $otherYear);

        // --------------------------------------------------
        $same = API_Date::__equals($date, $diffDate);
        // --------------------------------------------------

        self::assertFalse($same, 'dates ARE the same');
    }

    /**
     * Tests API_Date->updateFromArray()
     * @dataProvider provideDateParams
     */
    public function testUpdateFromArray(int $day, int $month, int  $year, bool $circa): void
    {
        $dateArray = self::makeDateArray(true, false, $day, $month, $year, $circa);

        $this->aPI_Date = new API_Date();

        // --------------------------------------------------
        $this->aPI_Date->updateFromArray($dateArray);
        // --------------------------------------------------

        self::assertDate($this->aPI_Date, false, $day, $month, $year, $circa);
    }

    /**
     * Tests API_Date->updateFromArray()
     * @dataProvider provideFullDateParams
     */
    public function testUpdateFromArrayFullDate(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        $dateArray = self::makeDateArray(true, true, $day, $month, $year, $circa, $endDay, $endMonth, $endYear, (int) $endCirca);

        $this->aPI_Date = new API_Date();

        // --------------------------------------------------
        $this->aPI_Date->updateFromArray($dateArray);
        // --------------------------------------------------

        self::assertDate($this->aPI_Date, true, $day, $month, $year, $circa, $endDay, $endMonth, $endYear, (int) $endCirca);
    }

    /**
     * Tests API_Date::newFromArray()
     * @dataProvider provideDateParams
     */
    public function testNewFromArray(int $day, int $month, int  $year, bool $circa): void
    {
        $dateArray = self::makeDateArray(true, false, $day, $month, $year, $circa);

        // --------------------------------------------------
        $this->aPI_Date = API_Date::newFromArray($dateArray);
        // --------------------------------------------------

        self::assertDate($this->aPI_Date, false, $day, $month, $year, $circa);
    }

    /**
     * Tests API_Date::newFromArray()
     * @dataProvider provideFullDateParams
     */
    public function testNewFromArrayFullDate(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        $dateArray = self::makeDateArray(true, true, $day, $month, $year, $circa, $endDay, $endMonth, $endYear, (int) $endCirca);

        // --------------------------------------------------
        $this->aPI_Date = API_Date::newFromArray($dateArray);
        // --------------------------------------------------

        self::assertDate($this->aPI_Date, true, $day, $month, $year, $circa, $endDay, $endMonth, $endYear, (int) $endCirca);
    }

    /**
     * Tests API_Date->makeParamArray()
     * @dataProvider provideDateParams
     */
    public function testMakeParamArray(int $day, int $month, int $year, bool $circa): void
    {
        $dateArray      = self::makeDateArray(true, false, $day, $month, $year, $circa);
        $this->aPI_Date = API_Date::newFromArray($dateArray);

        // --------------------------------------------------
        $actualArray = $this->aPI_Date->makeParamArray();
        // --------------------------------------------------

        self::assertEquals($dateArray, $actualArray, 'Arrays not equal');
    }

    /**
     * Tests API_Date->makeParamArray()
     * @dataProvider provideFullDateParams
     */
    public function testMakeParamArrayFullDate(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        $dateArray      = self::makeDateArray(true, true, $day, $month, $year, $circa, $endDay, $endMonth, $endYear, (int) $endCirca);
        $this->aPI_Date = API_Date::newFromArray($dateArray);

        // --------------------------------------------------
        $actualArray = $this->aPI_Date->makeParamArray();
        // --------------------------------------------------

        self::assertEquals($dateArray, $actualArray, 'Arrays not equal');
    }

    // -----------------------------------------------------
    // Tests for Baaad field names

    /**
     * Tests API_Date->updateFromArray()
     * @dataProvider provideDateParamsBadFields
     */
    public function testUpdateFromArrayBadFields(int $day, int $month, int  $year, string $fieldName): void
    {
        $dateArray = self::makeDateArray(true, false, $day, $month, $year, false);

        $dateArray [$fieldName] = rand(1, 9999);

        $this->aPI_Date = new API_Date();

        // --------------------------------------------------
        $this->aPI_Date->updateFromArray($dateArray);
        // --------------------------------------------------

        $actualArray = $this->aPI_Date->makeParamArray();

        self::assertDate($this->aPI_Date, false, $day, $month, $year, false);

        self::assertArrayNotHasKey($fieldName, $actualArray);
    }

    /**
     * Tests API_Date::newFromArray()
     * @dataProvider provideDateParamsBadFields
     */
    public function testNewFromArrayBadFields(int $day, int $month, int  $year, string $fieldName): void
    {
        $dateArray = self::makeDateArray(true, false, $day, $month, $year, false);

        $dateArray [$fieldName] = rand(1, 9999);

        // --------------------------------------------------
        $this->aPI_Date = API_Date::newFromArray($dateArray);
        // --------------------------------------------------

        $actualArray = $this->aPI_Date->makeParamArray();

        self::assertDate($this->aPI_Date, false, $day, $month, $year, false);

        self::assertArrayNotHasKey($fieldName, $actualArray);
    }

    // -----------------------------------------------------

    /**
     * Tests API_Date->toEpoch()
     * @dataProvider provideDateFormats
     */
    public function testToEpoch(int $day, int $month, int $year, int $epoch, string $timestamp, string $JSON): void
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $date = $dateFactory->makeDate($day, $month, $year, circa: false);

        // --------------------------------------------------
        $dateEpoch = $date->toEpoch();
        // --------------------------------------------------

        self::assertEquals($epoch, $dateEpoch);
    }

    /**
     * Tests API_Date->toTimestamp()
     * @dataProvider provideDateFormats
     */
    public function testToTimestamp(int $day, int $month, int $year, int $epoch, string $timestamp, string $JSON): void
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $date = $dateFactory->makeDate($day, $month, $year, circa: false);

        // --------------------------------------------------
        $actualTimestamp = $date->toTimestamp();
        // --------------------------------------------------

        self::assertEquals($timestamp, $actualTimestamp);
    }

    /*
     */
    /**
     * Tests API_Date->toTimestamp()
     * @param array  <int> $start
     * @param array  <int> $end
     * @param string $startTimestamp
     * @param string $endTimestamp
     * @param string $startJSON
     * @param string $endJSON
     * @dataProvider provideDateRangeFormats
     */
    public function testToTimestampFullDate(
        array  $start,
        array  $end,
        string $startTimestamp,
        string $endTimestamp,
        string $startJSON,
        string $endJSON,
    ): void {
        $date = self::makeFullDate($start, $end);

        $expectedTimestamp = "$startTimestamp-$endTimestamp";

        // --------------------------------------------------
        $actualTimestamp = $date->toTimestamp();
        // --------------------------------------------------

        self::assertEquals($expectedTimestamp, $actualTimestamp);
    }

    /**
     * Tests API_Date->toJSON()
     * @dataProvider provideDateFormats
     */
    public function testToJSON(int $day, int $month, int $year, int $epoch, string $timestamp, string $JSON): void
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $date = $dateFactory->makeDate($day, $month, $year, circa: false);

        // --------------------------------------------------
        $actualJSON = $date->toJSON();
        // --------------------------------------------------

        self::assertEquals($JSON, $actualJSON);
    }

    /**
     * Tests API_Date::fromEpoch()
     * @dataProvider provideDateFormats
     */
    public function testFromEpoch(int $day, int $month, int $year, int $epoch, string $timestamp, string $JSON): void
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $date = $dateFactory->makeDate($day, $month, $year, circa: false);

        // --------------------------------------------------
        $epochDate = API_Date::fromEpoch($epoch);
        // --------------------------------------------------

        self::assertEquals($date, $epochDate);
    }

    /**
     * Tests API_Date::fromTimestamp()
     * @dataProvider provideDateFormats
     */
    public function testFromTimestamp(int $day, int $month, int $year, int $epoch, string $timestamp, string $JSON): void
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $expected = $dateFactory->makeDate($day, $month, $year, circa: false);

        // --------------------------------------------------
        $actual = API_Date::fromTimestamp($timestamp);
        // --------------------------------------------------

        self::assertEquals($expected, $actual);
    }

    /**
     * Tests API_Date::fromTimestamp()
     * @param array  <int> $start
     * @param array  <int> $end
     * @param string $startTimestamp
     * @param string $endTimestamp
     * @param string $startJSON
     * @param string $endJSON
     * @dataProvider provideDateRangeFormats
     */
    public function testFromTimestampFullDate(
        array  $start,
        array  $end,
        string $startTimestamp,
        string $endTimestamp,
        string $startJSON,
        string $endJSON,
    ): void {
        $expected = self::makeFullDate($start, $end);

        // --------------------------------------------------
        $actual = API_Date::fromTimestamp($startTimestamp, $endTimestamp);
        // --------------------------------------------------

        self::assertEquals($expected, $actual);
    }

    /**
     * Tests API_Date::fromJSON()
     * @param int $day
     * @param int $month
     * @param int $year
     * @param int $epoch
     * @param string $timestamp
     * @param string $JSON
     * @dataProvider provideDateFormats
     */
    public function testFromJSON(int $day, int $month, int $year, int $epoch, string $timestamp, string $JSON): void
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $date = $dateFactory->makeDate($day, $month, $year, circa: false);

        // --------------------------------------------------
        $expected = API_Date::fromJSON($JSON);
        // --------------------------------------------------

        self::assertEquals($date, $expected);
    }

    /**
     * Tests API_Date::fromJSON()
     * @param array  <int> $start
     * @param array  <int> $end
     * @param string $startTimestamp
     * @param string $endTimestamp
     * @param string $startJSON
     * @param string $endJSON
     * @dataProvider provideDateRangeFormats
     */
    public function testFromJSONFullDate(
        array  $start,
        array  $end,
        string $startTimestamp,
        string $endTimestamp,
        string $startJSON,
        string $endJSON,
    ): void {
        $expected = self::makeFullDate($start, $end);

        // --------------------------------------------------
        $actual = API_Date::fromJSON($startJSON, $endJSON);
        // --------------------------------------------------

        self::assertEquals($expected, $actual);
    }

    /**
     * Tests API_Date::today()
     */
    public function testToday(): void
    {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $expected = $dateFactory->makeTodayDate();

        // --------------------------------------------------
        $actual = API_Date::today();
        // --------------------------------------------------

        self::assertEquals($expected, $actual);
    }

    /**
     * Tests API_Date->getDate()
     * @dataProvider provideDateParams
     */
    public function testGetDate(int $day, int $month, int  $year, bool $circa): void
    {
        $expectedArray = self::makeDateArray(true, false, $day, $month, $year, $circa);

        $this->aPI_Date = API_Date::newFromArray($expectedArray);

        // --------------------------------------------------
        $actualArray = $this->aPI_Date->getDate();
        // --------------------------------------------------

        self::assertEquals($expectedArray, $actualArray);
    }

    /**
     * Tests API_Date->getEndDate()
     * @dataProvider provideFullDateParams
     */
    public function testGetEndDate(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        $expectedArray = self::makeDateArray(false, true, 0, 0, 0, false, $endDay, $endMonth, $endYear, (int) $endCirca);

        $this->aPI_Date = API_Date::newFromArray($expectedArray);

        // --------------------------------------------------
        $actualArray = $this->aPI_Date->getEndDate();
        // --------------------------------------------------

        self::assertEquals($expectedArray, $actualArray);
    }

    /**
     * Tests API_Date->getRange()
     * @dataProvider provideDateWithRange
     */
    public function testGetRange(int $day, int $month, int  $year, bool $circa, string $range): void
    {
        $dateArray           = self::makeDateArray(true, false, $day, $month, $year, $circa);
        $dateArray ['range'] = $range;

        $this->aPI_Date = API_Date::newFromArray($dateArray);

        // --------------------------------------------------
        $actualRange = $this->aPI_Date->getRange();
        // --------------------------------------------------

        self::assertEquals($range, $actualRange);
    }

    /**
     * Tests API_Date->setDate()
     * @dataProvider provideDateParams
     */
    public function testSetDate(int $day, int $month, int  $year, bool $circa): void
    {
        $expectedArray = self::makeDateArray(true, false, $day, $month, $year, $circa);

        $this->aPI_Date = new API_Date();

        // --------------------------------------------------
        $this->aPI_Date->setDate($day, $month, $year, $circa);
        // --------------------------------------------------

        $actualArray = $this->aPI_Date->makeParamArray();

        self::assertEquals($expectedArray, $actualArray);
    }

    /**
     * Tests API_Date->setEndDate()
     * This uses the provider for a single-date and sets these values directly
     * to the end-date.
     * @dataProvider provideDateParams
     */
    public function testSetEndDate(int $endDay, int $endMonth, int $endYear, bool $endCirca): void
    {
        $expectedArray = self::makeDateArray(false, true, 0, 0, 0, false, $endDay, $endMonth, $endYear, (int) $endCirca);

        $this->aPI_Date = new API_Date();

        // --------------------------------------------------
        $this->aPI_Date->setEndDate($endDay, $endMonth, $endYear, $endCirca);
        // --------------------------------------------------

        $actualArray = $this->aPI_Date->makeParamArray();

        self::assertEquals($expectedArray, $actualArray);
    }

    /**
     * Tests API_Date->setRange()
     * @dataProvider provideDateWithRange
     */
    public function testSetRange(int $day, int $month, int  $year, bool $circa, string $range): void
    {
        $dateArray = self::makeDateArray(true, false, $day, $month, $year, $circa);

        $this->aPI_Date = API_Date::newFromArray($dateArray);

        // --------------------------------------------------
        $this->aPI_Date->setRange($range);
        // --------------------------------------------------

        $actualArray = $this->aPI_Date->makeParamArray();

        self::assertArrayHasKey('range', $actualArray);
        self::assertEquals($range, $actualArray ['range']);
    }

    /**
     * Tests API_Date->reset()
     * @dataProvider provideDateParams
     */
    public function testReset(int $day, int $month, int  $year, bool $circa): void
    {
        $dateArray = self::makeDateArray(true, false, $day, $month, $year, $circa);

        $this->aPI_Date = API_Date::newFromArray($dateArray);

        // --------------------------------------------------
        $this->aPI_Date->reset();
        // --------------------------------------------------

        $dateArray = $this->aPI_Date->makeParamArray();

        self::assertEmpty($dateArray, 'Date not empty.');
    }

    /**
     * Tests API_Date->getDay()
     * @dataProvider provideFullDateParams
     */
    public function testGetDay(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        self::assertDateField('day', $day, $month, $year, $circa, $endDay, $endMonth, $endYear, $endCirca);
    }

    /**
     * Tests API_Date->getMonth()
     * @dataProvider provideFullDateParams
     */
    public function testGetMonth(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        self::assertDateField('month', $day, $month, $year, $circa, $endDay, $endMonth, $endYear, $endCirca);
    }

    /**
     * Tests API_Date->getYear()
     * @dataProvider provideFullDateParams
     */
    public function testGetYear(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        self::assertDateField('year', $day, $month, $year, $circa, $endDay, $endMonth, $endYear, $endCirca);
    }

    /**
     * Tests API_Date->isCirca()
     * @dataProvider provideFullDateParams
     */
    public function testIsCirca(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        self::assertDateField('circa', $day, $month, $year, $circa, $endDay, $endMonth, $endYear, $endCirca);
    }

    /**
     * Tests API_Date->getEndDay()
     * @dataProvider provideFullDateParams
     */
    public function testGetEndDay(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        self::assertDateField('end_day', $day, $month, $year, $circa, $endDay, $endMonth, $endYear, $endCirca);
    }

    /**
     * Tests API_Date->getEndMonth(): void
     * @dataProvider provideFullDateParams
     */
    public function testGetEndMonth(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        self::assertDateField('end_month', $day, $month, $year, $circa, $endDay, $endMonth, $endYear, $endCirca);
    }

    /**
     * Tests API_Date->getEndYear(): void
     * @dataProvider provideFullDateParams
     */
    public function testGetEndYear(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        self::assertDateField('end_year', $day, $month, $year, $circa, $endDay, $endMonth, $endYear, $endCirca);
    }

    /**
     * Tests API_Date->isEndCirca(): void
     * @dataProvider provideFullDateParams
     */
    public function testIsEndCirca(
        int  $day,
        int  $month,
        int  $year,
        bool $circa,
        int  $endDay,
        int  $endMonth,
        int  $endYear,
        bool $endCirca,
    ): void {
        self::assertDateField('end_circa', $day, $month, $year, $circa, $endDay, $endMonth, $endYear, $endCirca);
    }

    /**
     * Tests API_Date->getSortKey(): void
    * @dataProvider provideDateParams
     */
    public function testGetSortKey(int $day, int $month, int  $year, bool $circa): void
    {
        $dateArray = self::makeDateArray(true, false, $day, $month, $year, $circa);

        $this->aPI_Date = API_Date::newFromArray($dateArray);

        $expectedSort = sprintf('%04d-%02d-%02d', $year, $month, $day);

        // --------------------------------------------------
        $actualSort = $this->aPI_Date->getSortKey();
        // --------------------------------------------------

        self::assertEquals($expectedSort, $actualSort);
    }
}
