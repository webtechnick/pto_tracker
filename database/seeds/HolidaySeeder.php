<?php

use App\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $year = date('Y');

        $holidays = [
            // Fixed holidays
            ['title' => 'New Year\'s Day', 'date' => $this->observedDate("{$year}-01-01")],
            ['title' => 'Independence Day', 'date' => $this->observedDate("{$year}-07-04")],
            ['title' => 'Christmas Eve', 'date' => "{$year}-12-24"],
            ['title' => 'Christmas Day', 'date' => $this->observedDate("{$year}-12-25")],
            ['title' => 'Day After Christmas', 'date' => "{$year}-12-26"],

            // Floating holidays
            ['title' => 'Good Friday', 'date' => $this->getGoodFriday($year)],
            ['title' => 'Memorial Day', 'date' => $this->getMemorialDay($year)],
            ['title' => 'Labor Day', 'date' => $this->getLaborDay($year)],
            ['title' => 'Thanksgiving', 'date' => $this->getThanksgiving($year)],
            ['title' => 'Day After Thanksgiving', 'date' => $this->getDayAfterThanksgiving($year)],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }

        // Add summer half-day Fridays (June through August)
        Holiday::bulkHalfFromRequest([
            'start' => "{$year}-06-01",
            'end' => "{$year}-08-31",
        ]);
    }

    /**
     * Get the observed date for a holiday (handles weekends)
     */
    private function observedDate($date)
    {
        $carbon = Carbon::parse($date);
        
        // If Saturday, observe on Friday
        if ($carbon->isSaturday()) {
            return $carbon->subDay()->toDateString();
        }
        
        // If Sunday, observe on Monday
        if ($carbon->isSunday()) {
            return $carbon->addDay()->toDateString();
        }

        return $carbon->toDateString();
    }

    /**
     * Get Good Friday (Friday before Easter)
     */
    private function getGoodFriday($year)
    {
        $easter = $this->calculateEaster($year);
        return $easter->subDays(2)->toDateString();
    }

    /**
     * Calculate Easter Sunday using the Anonymous Gregorian algorithm
     * Works without PHP's calendar extension
     */
    private function calculateEaster($year)
    {
        $a = $year % 19;
        $b = floor($year / 100);
        $c = $year % 100;
        $d = floor($b / 4);
        $e = $b % 4;
        $f = floor(($b + 8) / 25);
        $g = floor(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = floor($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = floor(($a + 11 * $h + 22 * $l) / 451);
        $month = floor(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::createFromDate($year, $month, $day);
    }

    /**
     * Get Memorial Day (last Monday of May)
     */
    private function getMemorialDay($year)
    {
        return Carbon::parse("last monday of may {$year}")->toDateString();
    }

    /**
     * Get Labor Day (first Monday of September)
     */
    private function getLaborDay($year)
    {
        return Carbon::parse("first monday of september {$year}")->toDateString();
    }

    /**
     * Get Thanksgiving (fourth Thursday of November)
     */
    private function getThanksgiving($year)
    {
        return Carbon::parse("fourth thursday of november {$year}")->toDateString();
    }

    /**
     * Get Day After Thanksgiving (Friday)
     */
    private function getDayAfterThanksgiving($year)
    {
        $thanksgiving = Carbon::parse("fourth thursday of november {$year}");
        return $thanksgiving->addDay()->toDateString();
    }
}
