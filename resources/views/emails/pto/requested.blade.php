<div>
    <h1>Paid Time Off Request</h1>
    <ul>
        <li>Employee: {{ $pto->employee->name }}</li>
        <li>Start: {{ $pto->start_time->toDayDateTimeString() }}</li>
        <li>End: {{ $pto->end_time->toDayDateTimeString() }}</li>
        <li>Days: {{ $pto->days }}</li>
    </ul>
    <p><a href="{{ config('app.url') }}">PTO Tracker</a></p>

    <p>Your Trusty Webbot</p>
</div>