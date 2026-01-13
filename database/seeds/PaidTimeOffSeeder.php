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
        $year = date('Y');

        // PTO data uses month-day format, year is added dynamically
        $paidtimeoffs = [
            'Desmond Rush' => [
                ['start' => '02-14', 'end' => '02-14'],
                ['start' => '06-20', 'end' => '06-24'],
                ['start' => '11-27', 'end' => '11-29'],
            ],
            'Drew Glaser' => [
                ['start' => '03-15', 'end' => '03-15'],
                ['start' => '07-03', 'end' => '07-07'],
                ['start' => '12-23', 'end' => '12-27'],
            ],
            'Dillon Formo' => [
                ['start' => '04-17', 'end' => '04-17'],
                ['start' => '06-02', 'end' => '06-02'],
                ['start' => '07-05', 'end' => '07-05'],
                ['start' => '11-27', 'end' => '11-27'],
                ['start' => '12-22', 'end' => '12-27'],
            ],
            'Elanor Riley' => [
                ['start' => '01-15', 'end' => '01-15'],
                ['start' => '05-24', 'end' => '05-28'],
                ['start' => '09-04', 'end' => '09-04'],
            ],
            'Eric Pridham' => [
                ['start' => '02-20', 'end' => '02-21'],
                ['start' => '08-14', 'end' => '08-18'],
                ['start' => '12-26', 'end' => '12-29'],
            ],
            'Joey Agpawa' => [
                ['start' => '03-10', 'end' => '03-10'],
                ['start' => '06-19', 'end' => '06-23'],
                ['start' => '11-22', 'end' => '11-24'],
            ],
            'Lyle Smart' => [
                ['start' => '05-30', 'end' => '05-31'],
                ['start' => '06-01', 'end' => '06-09'],
                ['start' => '12-20', 'end' => '12-29'],
            ],
            'Mark Vince' => [
                ['start' => '01-25', 'end' => '01-25'],
                ['start' => '03-23', 'end' => '03-24'],
                ['start' => '04-03', 'end' => '04-07'],
                ['start' => '10-09', 'end' => '10-13'],
                ['start' => '11-22', 'end' => '11-22'],
                ['start' => '12-21', 'end' => '12-29'],
            ],
            'Nick Baker' => [
                ['start' => '02-20', 'end' => '02-21'],
                ['start' => '03-01', 'end' => '03-01'],
                ['start' => '05-25', 'end' => '05-31'],
                ['start' => '08-11', 'end' => '08-14'],
                ['start' => '12-18', 'end' => '12-29'],
            ],
            'Rachelle Zani' => [
                ['start' => '02-09', 'end' => '02-09'],
                ['start' => '07-05', 'end' => '07-14'],
                ['start' => '08-21', 'end' => '08-21'],
                ['start' => '09-21', 'end' => '09-21'],
                ['start' => '11-22', 'end' => '11-22'],
                ['start' => '12-27', 'end' => '12-29'],
            ],
            'Valerie England' => [
                ['start' => '02-03', 'end' => '02-03'],
                ['start' => '04-28', 'end' => '04-28'],
                ['start' => '09-11', 'end' => '09-15'],
                ['start' => '12-21', 'end' => '12-29'],
            ],
            'Tyler Shaw' => [
                ['start' => '02-09', 'end' => '02-14'],
                ['start' => '07-05', 'end' => '07-07'],
                ['start' => '11-20', 'end' => '11-22'],
                ['start' => '12-27', 'end' => '12-29'],
            ],
        ];

        foreach ($paidtimeoffs as $employeeName => $ptos) {
            $employee = Employee::where('name', $employeeName)->first();
            
            if (!$employee) {
                continue;
            }

            foreach ($ptos as $pto) {
                PaidTimeOff::create([
                    'employee_id' => $employee->id,
                    'start_time' => "{$year}-{$pto['start']}",
                    'end_time' => "{$year}-{$pto['end']}",
                    'is_approved' => true,
                ]);
            }
        }
    }
}
