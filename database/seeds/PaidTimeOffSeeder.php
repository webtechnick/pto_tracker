<?php

use App\Employee;
use App\PaidTimeOff;
use Illuminate\Database\Seeder;

class PaidTimeOffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paidtimeoffs = [
            'Aaron Hall' => [
                ['start_time' => '2017-01-25', 'end_time' => '2017-01-25'],
                ['start_time' => '2017-02-24', 'end_time' => '2017-02-24'],
            ],
            'Ben Stout' => [
                ['start_time' => '2017-02-20', 'end_time' => '2017-02-20'],
                ['start_time' => '2017-03-20', 'end_time' => '2017-03-24'],
                ['start_time' => '2017-04-17', 'end_time' => '2017-04-17'],
                ['start_time' => '2017-06-09', 'end_time' => '2017-06-09'],
                ['start_time' => '2017-07-10', 'end_time' => '2017-07-14'],
                ['start_time' => '2017-12-20', 'end_time' => '2017-12-29'],
            ],
            'Craig Knowles' => [
            ],
            'Dillon Formo' => [
                ['start_time' => '2017-04-17', 'end_time' => '2017-04-17'],
                ['start_time' => '2017-06-02', 'end_time' => '2017-06-02'],
                ['start_time' => '2017-07-05', 'end_time' => '2017-07-05'],
                ['start_time' => '2017-11-27', 'end_time' => '2017-11-27'],
                ['start_time' => '2017-12-22', 'end_time' => '2017-12-27'],
            ],
            'Jeff Kolesnikowicz' => [
            ],
            'Laura Kimpel-Matthews' => [
                ['start_time' => '2017-05-24', 'end_time' => '2017-05-31'],
                ['start_time' => '2017-08-28', 'end_time' => '2017-08-31'],
                ['start_time' => '2017-11-20', 'end_time' => '2017-11-22'],
                ['start_time' => '2017-12-27', 'end_time' => '2017-12-29'],
            ],
            'Lyle Smart' => [
                ['start_time' => '2017-05-30', 'end_time' => '2017-05-31'],
                ['start_time' => '2017-06-01', 'end_time' => '2017-06-09'],
                ['start_time' => '2017-12-20', 'end_time' => '2017-12-29'],
            ],
            'Mark Vince' => [
                ['start_time' => '2017-01-25', 'end_time' => '2017-01-25'],
                ['start_time' => '2017-03-23', 'end_time' => '2017-03-24'],
                ['start_time' => '2017-04-03', 'end_time' => '2017-04-07'],
                ['start_time' => '2017-10-09', 'end_time' => '2017-10-13'],
                ['start_time' => '2017-11-22', 'end_time' => '2017-11-22'],
                ['start_time' => '2017-12-21', 'end_time' => '2017-12-29'],
            ],
            'Nick Baker' => [
                ['start_time' => '2017-02-20', 'end_time' => '2017-02-21'],
                ['start_time' => '2017-03-01', 'end_time' => '2017-03-01'],
                ['start_time' => '2017-05-25', 'end_time' => '2017-05-31'],
                ['start_time' => '2017-08-11', 'end_time' => '2017-08-14'],
                ['start_time' => '2017-12-18', 'end_time' => '2017-12-29'],
            ],
            'Rachelle Zani' => [
                ['start_time' => '2017-02-09', 'end_time' => '2017-02-09'],
                ['start_time' => '2017-07-05', 'end_time' => '2017-07-14'],
                ['start_time' => '2017-08-21', 'end_time' => '2017-08-21'],
                ['start_time' => '2017-09-21', 'end_time' => '2017-09-21'],
                ['start_time' => '2017-11-22', 'end_time' => '2017-11-22'],
                ['start_time' => '2017-12-27', 'end_time' => '2017-12-29'],
            ],
            'Travis Reddell' => [
                ['start_time' => '2017-02-03', 'end_time' => '2017-02-03'],
                ['start_time' => '2017-02-24', 'end_time' => '2017-02-24'],
                ['start_time' => '2017-03-15', 'end_time' => '2017-03-15'],
                ['start_time' => '2017-04-07', 'end_time' => '2017-04-07'],
                ['start_time' => '2017-04-28', 'end_time' => '2017-04-28'],
                ['start_time' => '2017-05-19', 'end_time' => '2017-05-19'],
                ['start_time' => '2017-06-09', 'end_time' => '2017-06-09'],
                ['start_time' => '2017-09-11', 'end_time' => '2017-09-15'],
                ['start_time' => '2017-10-20', 'end_time' => '2017-10-20'],
                ['start_time' => '2017-12-21', 'end_time' => '2017-12-29'],
            ],
            'Tyler Shaw' => [
                ['start_time' => '2017-02-09', 'end_time' => '2017-02-14'],
                ['start_time' => '2017-07-05', 'end_time' => '2017-07-07'],
                ['start_time' => '2017-11-20', 'end_time' => '2017-11-22'],
                ['start_time' => '2017-12-27', 'end_time' => '2017-12-29'],
            ],
        ];

        foreach ($paidtimeoffs as $employee_name => $ptos) {
            $id = Employee::where('name', $employee_name)->getField('id');
            foreach ($ptos as $pto) {
                $pto['employee_id'] = $id;
                $pto['is_approved'] = true;
                PaidTimeOff::create($pto);
            }
        }
    }
}
