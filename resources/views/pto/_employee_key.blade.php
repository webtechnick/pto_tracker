<div class="panel panel-default">
    <div class="panel-heading">
        <strong>All Resource Units</strong>
    </div>
    <div class="panel-body">
        <ul class="list-group">
            <li class="list-group-item">
                <span class="employee" style="background-color: black; color: white;">Resource Unit</span>
                <span class="badge"><strong>## PTO Days left</strong></span>
            </li>
            @foreach($employees as $employee)
            <li class="list-group-item">
                <span class="employee" style="background-color: {{ $employee->bgcolor }}; color: {{ $employee->color }};">{{ $employee->name }}</span>
                <span class="badge"><strong>{{ $employee->daysLeft($year) }}</strong></span>
                <span>
                    @if($employee->daysLeft($year) != $employee->pendingDaysLeft($year))
                        <small>(Pending Left: {{ $employee->pendingDaysLeft($year) }})</small>
                    @endif
                </span>
                <!-- <div class="row">
                    <div class="col-md-8">

                    </div>
                    <div class="col-md-4">

                    </div>
                </div> -->



            </li>
            @endforeach
        </ul>
    </div>
</div>