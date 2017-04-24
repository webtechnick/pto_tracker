@extends('layouts.app')
@section('content')

    <div class="container">
        <h1>Currently On Call</h1>

        <div class="panel panel-default">
            <div class="panel-body">
                <span class="name">{{ env('ALWAYS_ON_CALL_NAME') }}</span> <span class="number pull-right">{{ env('ALWAYS_ON_CALL_NUMBER') }}</span>
            </div>
        </div>

        @foreach ($onCallEmployees as $employee)
            @include('employees._person', ['employee' => $employee])
        @endforeach
    </div>

@endsection