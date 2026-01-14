<div class="panel panel-default employee-key-panel">
    <div class="panel-heading">
        <strong>All Resource Units</strong>
        <span class="badge employee-count">{{ $employees->count() }}</span>
    </div>
    <div class="panel-body employee-key-body">
        <ul class="list-group">
            <li class="list-group-item">
                <span class="employee" style="background-color: black; color: white;">Resource Unit</span>
                <span class="badge"><strong>## PTO Days left</strong></span>
            </li>
            @foreach($employees as $employee)
            <li class="list-group-item">
                <span class="employee" style="background-color: {{ $employee->bgcolor }}; color: {{ $employee->color }};">{{ $employee->name }}</span>

                @if ($employee->canViewPto())
                    <span class="badge"><strong>{{ $employee->daysLeft($year) }}</strong></span>
                    @if($employee->daysLeft($year) != $employee->pendingDaysLeft($year))
                        <small class="pending-pto">(Pending: {{ $employee->pendingDaysLeft($year) }})</small>
                    @endif
                @endif
            </li>
            @endforeach
        </ul>
    </div>
</div>
