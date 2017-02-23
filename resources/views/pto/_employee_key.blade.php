<div class="panel panel-default">
    <div class="panel-heading">
        <strong>All Resource Units</strong>
    </div>
    <div class="panel-body">
        <ul class="list-group">
            @foreach($employees as $employee)
            <li class="list-group-item">
                <h5><span class="employee" style="background-color: {{ $employee->bgcolor }}; color: {{ $employee->color }};">{{ $employee->name }}</span></h5>
                <span>
                    <strong>{{ $employee->daysLeft($year) }}</strong> PTO day(s) left.
                    @if($employee->daysLeft($year) != $employee->pendingDaysLeft($year))
                        <small>(Pending Left: {{ $employee->pendingDaysLeft($year) }})</small>
                    @endif
                </span>
            </li>
            @endforeach
        </ul>
    </div>
</div>