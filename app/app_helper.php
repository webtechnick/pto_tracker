<?php
function getDates($year)
{
    $dates = array();

    for($i = 1; $i <= 366; $i++) {
        $month = date('m', mktime(0,0,0,1,$i,$year));
        $wk = date('W', mktime(0,0,0,1,$i,$year));
        $wkDay = date('D', mktime(0,0,0,1,$i,$year));
        $day = date('d', mktime(0,0,0,1,$i,$year));

        $dates[$month][$wk][$day] = $wkDay;
    }

    return $dates;
}