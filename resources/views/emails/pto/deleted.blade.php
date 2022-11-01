<div>
    <h1>Paid Time Off Deleted.</h1>

    <p>I regret to inform you that your PTO request of:</p>

    <ul>
        <li>Start: {{ $pto->start_time->toDayDateTimeString() }}</li>
        <li>End: {{ $pto->end_time->toDayDateTimeString() }}</li>
        <li>Days: {{ $pto->days }}</li>
    </ul>

    <p>Was removed, Resource Unit. If I had feelings, this news would sadden PTO Tracker. <small>(Please request to install FeelingsV3 to my developers)</small></p>

    <p>Warm Binary Regards,<br>
    Your Overly-Supportive <a href="{{ config('app.url') }}">PTO Tracker</a></p>

    <p>P.S. This message was transmitted on 100% recycled electrons.</p>
</div>