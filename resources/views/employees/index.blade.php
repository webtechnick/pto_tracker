@extends('layouts.app')
@section('content')
    <div class="container">
        @foreach($employees as $employee)
            <div class="panel panel-default">
                <div class="panel-body">
                    <span class="name">{{ $employee->name }}</span>
                    <div class="pull-right">
                        <div class="btn-group" role="group" aria-label="...">
                            @if ($employee->isOnCall())
                                <a href="/admin/employees/oncall/clear" class="btn btn-danger">Clear On Call</a>
                            @else
                                <a href="/admin/employees/oncall/set/{{ $employee->id }}" class="btn btn-success">Set On Call</a>
                            @endif
                            <a href="{{ route('admin.employees.edit', [$employee]) }}" class="btn btn-default">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection