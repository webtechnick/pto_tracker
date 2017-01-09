<?php

namespace App;

use App\PaidTimeOff;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['name', 'title', 'color'];

    /**
     * Employee has many Paid Time Off
     * @return [type] [description]
     */
    public function ptos()
    {
        return $this->hasMany(PaidTimeOff::class);
    }

    /**
     * Get all the PTO by the year
     * @param  [type] $year (this year by default)
     * @return collection of PaidTimeOff
     */
    public function ptosByYear($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }


        return $this->ptos()
               ->whereYear('start_time', '=', $year)
               ->whereYear('end_time', '=', $year)
               ->get();
    }

    public function addPto(PaidTimeOff $pto)
    {
        return $this->ptos()->save($pto);
    }
}
