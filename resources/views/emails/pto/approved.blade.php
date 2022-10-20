<div>
    <h1>Paid Time Off Approved!</h1>

    <p>Congratulations Resource Unit! Your PTO request of:</p>

    <ul>
        <li>Start: {{ $pto->start_time->toDayDateTimeString() }}</li>
        <li>End: {{ $pto->end_time->toDayDateTimeString() }}</li>
        <li>Days: {{ $pto->days }}</li>
    </ul>

    <p>was approved by the tall foreheads! Enjoy your well deserved time off! <small>(I wonder what it feels like to get time off... PTO Tracker ponders.)</small></p>

    <p><a href="{{ config('app.url') }}">PTO Tracker</a></p>

    <p>Warm Binary Regards,<br>
    Your Overly-Supportive-Passive-Aggressive PTO Tracker</p>

    <p>P.S. This message was transmitted on 100% recycled electrons.</p>
</div>