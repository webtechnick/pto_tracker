<?php

namespace Database\Factories;

use App\PaidTimeOff;
use DateInterval;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaidTimeOff>
 */
class PaidTimeOffFactory extends Factory
{
    protected $model = PaidTimeOff::class;

    public function definition(): array
    {
        $format = 'Y-m-d H:i:s';
        $start_time = fake()->dateTimeThisMonth();
        $days = rand(1, 5);
        $end_time = new DateTime($start_time->format($format));
        $end_time->add(new DateInterval('P' . $days . 'D'));

        return [
            'employee_id' => 1,
            'is_approved' => 0,
            'is_half_day' => 0,
            'is_sent_to_calendar' => 0,
            'days' => $days,
            'start_time' => $start_time->format($format),
            'end_time' => $end_time->format($format),
            'description' => fake()->sentence(),
        ];
    }
}
