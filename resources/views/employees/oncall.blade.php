@extends('layouts.app')
@section('content')

    <div class="container">
        <h1>Currently On Call</h1>

        <div class="panel panel-default">
            <div class="panel-body">
                <span class="name">{{ env('ALWAYS_ON_CALL_NAME') }}</span> <span class="number pull-right">{{ env('ALWAYS_ON_CALL_NUMBER') }}</span>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">
                Oncall rotation is now handled via OpsGenie.<br><br>

                Please contact Zeb (in slack or via phone) for instructions.
            </div>
        </div>

        {{-- @foreach ($onCallEmployees as $employee)
            @include('employees._person', ['employee' => $employee])
        @endforeach --}}

        <div class="jumbotron">
            <p>Only notify an on-call developer after hours if there is an emergency related to our product line.</p>
            <p>Things that are considered emergencies:
                <ul>
                    <li>Fire tickets</li>
                    <li>Important sections of the website unresponsive/down</li>
                    <li>Entire website is down</li>
                </ul>
            </p>
        </div>
    </div>

@endsection