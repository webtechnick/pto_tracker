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
            'Elanor Riley' => [
                ['start' => '03-15', 'end' => '03-19'],
                ['start' => '12-21', 'end' => '12-31'],
            ],
            'Ben Stout' => [
                ['start' => '06-10', 'end' => '06-14'],
                ['start' => '12-23', 'end' => '12-31'],
            ],
            'Drew Glaser' => [
                ['start' => '04-07', 'end' => '04-11'],
                ['start' => '12-18', 'end' => '12-24'],
            ],
            'Dillon Formo' => [
                ['start' => '04-17', 'end' => '04-17'],
                ['start' => '06-02', 'end' => '06-02'],
                ['start' => '07-05', 'end' => '07-05'],
                ['start' => '11-27', 'end' => '11-27'],
                ['start' => '12-22', 'end' => '12-31'],
            ],
            'Emily Chen' => [
                ['start' => '02-14', 'end' => '02-14'],
                ['start' => '08-05', 'end' => '08-09'],
                ['start' => '12-19', 'end' => '12-26'],
            ],
            'Frank Torres' => [
                ['start' => '05-26', 'end' => '05-30'],
                ['start' => '12-20', 'end' => '12-31'],
            ],
            'Grace Kim' => [
                ['start' => '03-03', 'end' => '03-07'],
                ['start' => '09-15', 'end' => '09-19'],
                ['start' => '12-21', 'end' => '12-27'],
            ],
            'Henry Patel' => [
                ['start' => '07-14', 'end' => '07-18'],
                ['start' => '12-18', 'end' => '12-31'],
            ],
            'Isabella Martinez' => [
                ['start' => '04-21', 'end' => '04-25'],
                ['start' => '12-23', 'end' => '12-31'],
            ],
            'Jake Williams' => [
                ['start' => '06-23', 'end' => '06-27'],
                ['start' => '12-19', 'end' => '12-24'],
            ],
            'Karen Liu' => [
                ['start' => '02-03', 'end' => '02-07'],
                ['start' => '10-06', 'end' => '10-10'],
                ['start' => '12-21', 'end' => '12-31'],
            ],
            'Leo Thompson' => [
                ['start' => '08-18', 'end' => '08-22'],
                ['start' => '12-22', 'end' => '12-29'],
            ],
            'Lyle Smart' => [
                ['start' => '05-30', 'end' => '05-31'],
                ['start' => '06-01', 'end' => '06-09'],
                ['start' => '12-20', 'end' => '12-31'],
            ],
            'Mark Vince' => [
                ['start' => '01-25', 'end' => '01-25'],
                ['start' => '03-23', 'end' => '03-24'],
                ['start' => '04-03', 'end' => '04-07'],
                ['start' => '10-09', 'end' => '10-13'],
                ['start' => '11-22', 'end' => '11-22'],
                ['start' => '12-21', 'end' => '12-31'],
            ],
            'Nina Rodriguez' => [
                ['start' => '03-31', 'end' => '04-04'],
                ['start' => '12-18', 'end' => '12-26'],
            ],
            'Nick Baker' => [
                ['start' => '02-20', 'end' => '02-21'],
                ['start' => '03-01', 'end' => '03-01'],
                ['start' => '05-25', 'end' => '05-31'],
                ['start' => '08-11', 'end' => '08-14'],
                ['start' => '12-18', 'end' => '12-31'],
            ],
            'Oscar Davis' => [
                ['start' => '07-07', 'end' => '07-11'],
                ['start' => '12-19', 'end' => '12-29'],
            ],
            'Rachelle Zani' => [
                ['start' => '02-09', 'end' => '02-09'],
                ['start' => '07-05', 'end' => '07-14'],
                ['start' => '08-21', 'end' => '08-21'],
                ['start' => '09-21', 'end' => '09-21'],
                ['start' => '11-22', 'end' => '11-22'],
                ['start' => '12-22', 'end' => '12-31'],
            ],
            'Sam Johnson' => [
                ['start' => '05-12', 'end' => '05-16'],
                ['start' => '12-20', 'end' => '12-31'],
            ],
            'Valerie England' => [
                ['start' => '02-09', 'end' => '02-14'],
                ['start' => '07-05', 'end' => '07-07'],
                ['start' => '11-20', 'end' => '11-22'],
                ['start' => '12-21', 'end' => '12-27'],
            ],
            'Tyler Shaw' => [
                ['start' => '02-09', 'end' => '02-14'],
                ['start' => '07-05', 'end' => '07-07'],
                ['start' => '11-20', 'end' => '11-22'],
                ['start' => '12-23', 'end' => '12-31'],
            ],
            // Test users - Manager has some PTO
            'Manager' => [
                ['start' => '03-10', 'end' => '03-14'],
                ['start' => '07-21', 'end' => '07-25'],
                ['start' => '12-23', 'end' => '12-31'],
            ],
            // Employee user - mix of past and future PTO for testing cancel feature
            'Employee' => [
                ['start' => '01-06', 'end' => '01-10', 'approved' => true],  // Past - cannot cancel
                ['start' => '02-17', 'end' => '02-21', 'approved' => true],  // Future - can cancel
                ['start' => '04-14', 'end' => '04-18', 'approved' => false], // Future pending - can cancel
                ['start' => '06-16', 'end' => '06-20', 'approved' => true],  // Future - can cancel
                ['start' => '08-25', 'end' => '08-29', 'approved' => true],  // Future - can cancel
                ['start' => '12-22', 'end' => '12-31', 'approved' => true],  // Future - can cancel
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
                    'is_approved' => $pto['approved'] ?? true,
                ]);
            }
        }
    }
}
