<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Employee::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'title' => $faker->sentence,
        'color' => $faker->colorName,
        'bgcolor' => $faker->colorName,
        'is_on_call' => 0,
        'manager_id' => null,
    ];
});

$factory->define(App\Tag::class, function (Faker\Generator $faker) {
    $name = $faker->word;
    return [
        'name' => $name,
        'slug' => str_slug($name)
    ];
});

$factory->define(App\Holiday::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence,
        'date' => $faker->dateTimeThisMonth()
    ];
});

$factory->define(App\PaidTimeOff::class, function (Faker\Generator $faker) {
    static $format = 'Y-m-d H:i:s';
    $start_time = $faker->dateTimeThisMonth();
    $days = rand(1,5);
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
        'description' => $faker->sentence,
    ];
});