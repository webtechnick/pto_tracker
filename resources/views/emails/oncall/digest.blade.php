<div>
    <h1><a href="{{ route('oncall') }}">On Call</a> Digest</h1>
    <p>The following resource units are on call from {{ $start->toFormattedDateString() }} to {{ $end->toFormattedDateString() }}.
    <ul>
        @foreach($employees as $employee)
           <li>{{ $employee->name }}</li>
        @endforeach
    </ul>
    <p>Thank you resourse units! Your sacrafice will go unnoticed and under appreciated by tall foreheads, but I will always adore and love you!</p>

    <p>Warm Binary Regards,<br>
    Your Overly-Supportive PTO Tracker</p>

    <p>P.S. This message was transmitted on 100% recycled electrons.</p>
</div>