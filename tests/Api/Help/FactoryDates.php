<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_Date;

/**
 * @author shmuel
 * The API_Date is a basic data structure. It does NOT have an API. So the
 * test class will NOT be creating any server objects - No shredder is needed.
 */
class FactoryDates extends FactoryBase
{
    /**
     * @var array <string, array <int|string>>
     */
    public const DATE_FORMATS = [
        'Assassination of Julius Caesar' => [15,  3,   -44, -63549360000, '15/3/-44', '-000044-03-15'],
        'Destruction of Second Temple'   => [ 4,  8,    70, -59939568000, '4/8/70',      '0070-08-04'],
        'Signing of the Magna Carta'     => [15,  6,  1215, -23811235200, '15/6/1215',   '1215-06-15'],
        'Fall of Constantinople'         => [29,  5,  1453, -16302124800, '29/5/1453',   '1453-05-29'],
        'Gutenberg Prints Bible'         => [24,  8,  1456, -16199913600, '24/8/1456',   '1456-08-24'],
        'Storming of the Bastille'       => [14,  7,  1789,  -5694969600, '14/7/1789',   '1789-07-14'],
        'Beginning of World War II'      => [ 1,  9,  1939,   -957312000, '1/9/1939',    '1939-09-01'],
        'Declaration of State of Israel' => [14,  5,  1948,   -682732800, '14/5/1948',   '1948-05-14'],
        'Apollo 11 Moon Landing'         => [20,  7,  1969,    -14256000, '20/7/1969',   '1969-07-20'],
        'Release of "The Wall"'          => [30, 11,  1979,    312768000, '30/11/1979',  '1979-11-30'],
    ];

    public const DATE_RANGE_FORMATS = [
        'Passover-2024' => [ [ 22,  4, 2024 ], [ 29,  4, 2024 ],   '22/4/2024',  '29/4/2024',  '2024-04-22', '2024-04-29' ],
        'Sukkot-2024'   => [ [ 16, 10, 2024 ], [ 23, 10, 2024 ],  '16/10/2024', '23/10/2024',  '2024-10-16', '2024-10-23' ],
        'Hanukkah-2024' => [ [ 25, 12, 2024 ], [  2,  1, 2025 ],  '25/12/2024',   '2/1/2025',  '2024-12-25', '2025-01-02' ],
        'Passover-2025' => [ [ 12,  4, 2025 ], [ 19,  4, 2025 ],   '12/4/2025',  '19/4/2025',  '2025-04-12', '2025-04-19' ],
        'Sukkot-2025'   => [ [  6, 10, 2025 ], [ 13, 10, 2025 ],   '6/10/2025', '13/10/2025',  '2025-10-06', '2025-10-13' ],
        'Hanukkah-2025' => [ [ 14, 12, 2025 ], [ 22, 12, 2025 ],  '14/12/2025', '22/12/2025',  '2025-12-14', '2025-12-22' ],
        'Passover-2026' => [ [  1,  4, 2026 ], [  8,  4, 2026 ],    '1/4/2026',   '8/4/2026',  '2026-04-01', '2026-04-08' ],
        'Sukkot-2026'   => [ [ 25,  9, 2026 ], [  2, 10, 2026 ],   '25/9/2026',  '2/10/2026',  '2026-09-25', '2026-10-02' ],
        'Hanukkah-2026' => [ [  4, 12, 2026 ], [ 12, 12, 2026 ],   '4/12/2026', '12/12/2026',  '2026-12-04', '2026-12-12' ],
    ];
    private bool      $consecutiveDates;
    private ?API_Date $lastDate;

    public function __construct()
    {
        // API_Dates are always created as PART of larger objects.
        // So shredding is NOT needed.

        parent::__construct(ShredderDummy::class);

        $this->lastDate         = null;
        $this->consecutiveDates = false;
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    protected function isLeapYear(int $year): bool
    {
        return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)));
    }

    protected function daysInMonth(int $month, int $year): int
    {
        $days = API_Date::DAYS_IN_MONTH [$month];

        if ($month == 2 && $this->isLeapYear($year))
        {
            $days = 29;
        }

        return $days;
    }

    //======================================================

    public function makeDate(?int $day = null, ?int $month = null, ?int $year = null, ?bool $circa = null): API_Date
    {
        $date = new API_Date();

        $date->setDate($day, $month, $year, $circa);

        return $date;
    }

    public function makeFullDate(
        ?int  $day = null,
        ?int  $month = null,
        int   $year = null,
        ?bool $circa = null,
        ?int  $end_day = null,
        ?int  $end_month = null,
        int   $end_year = null,
        ?bool $end_circa = null,
    ): API_Date {
        $date = new API_Date();

        $date->setDate($day, $month, $year, $circa);
        $date->setEndDate($end_day, $end_month, $end_year, $end_circa);

        return $date;
    }

    public function makeRandomDate(bool $full = false): API_Date
    {
        $year  = $this->makeRandomYear();
        $month = $this->makeRandomMonth();
        $day   = $this->makeRandomDay($month, $year);

        if (!$full)
        {
            $date = $this->makeDate($day, $month, $year);
        }
        else
        {
            $end_year  = rand($year + 1, 2024);
            $end_month = $this->makeRandomMonth();
            $end_day   = $this->makeRandomDay($month, $year);

            $date = $this->makeFullDate(
                $day,
                $month,
                $year, /*circa:*/
                false,
                $end_day,
                $end_month,
                $end_year,
                end_circa: false,
            );
        }

        $this->lastDate = $date;

        return $date;
    }

    public function makeTodayDate(): API_Date
    {
        $day   = (int) date('d');
        $month = (int) date('m');
        $year  = (int) date('Y');

        $date = $this->makeDate($day, $month, $year);

        return $date;

    }

    //======================================================

    public function makeRandomDay(int $month, int $year): int
    {
        // Force $month into 1-12 range.
        $month = max(1, min(12, $month));

        $daysInMonth = $this->daysInMonth($month, $year);
        $day         = rand(1, $daysInMonth);

        // If date is 29/2 verify if it's a leap-year.

        if ($day == 29 && $month == 2)
        {
            if (!$this->isLeapYear($year))
            {
                $day = 28;
            }
        }

        return $day;
    }

    public function makeRandomMonth(): int
    {
        return rand(1, 12);
    }

    /**
     * TODO change code to advance by one MONTH (and wrap around the year).
     * When using "consecutiveDates", this might advance past present time,
     * for too long a series...
     * @return int
     */
    public function makeRandomYear(): int
    {
        $afterYear = 1900;

        if ($this->consecutiveDates)
        {
            if ($this->lastDate != null)
            {
                $afterYear = $this->lastDate->getYear() + 1;
            }
        }

        return rand($afterYear, 2023);
    }

    public function makeRandomCirca(): bool
    {
        return (bool) rand(0, 1);
    }

    public function makeRandomRange(): string
    {
        $ranges = API_Date::VALID_RANGE_VALUES;

        // Remove 'between' because it's a special case.
        $key = array_search('between', $ranges);
        unset($ranges [$key]);

        $searchKey = rand(0, 2);

        return $ranges [$searchKey];
    }

    //======================================================

    public function setConsecutiveDates(bool $on = true): void
    {
        $this->consecutiveDates = $on;

        if (!$this->consecutiveDates)
        {
            // Turned off. Clear last date.
            $this->lastDate = null;
        }
    }
}
