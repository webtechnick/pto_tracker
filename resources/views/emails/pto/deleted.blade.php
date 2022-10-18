<div>
    <h1>Paid Time Off Deleted</h1>

    <p>Your PTO request of:</p>
    <ul>
        <li>Start: {{ $pto->start_time->toDayDateTimeString() }}</li>
        <li>End: {{ $pto->end_time->toDayDateTimeString() }}</li>
        <li>Days: {{ $pto->days }}</li>
    </ul>

    <p>Was removed</p>

    <p><a href="{{ config('app.url') }}">PTO Tracker</a></p>

    <p>Your Trusty Webbot</p>
</div>