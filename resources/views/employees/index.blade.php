@extends('layouts.app')
@section('content')
    <div class="container">
        @foreach($employees as $employee)
            <div class="panel panel-default">
                <div class="panel-body">
                    <span class="name">{{ $employee->name }}</span> <small>(Teams: {{ $employee->tag_string ?: 'None'}})</small>
                    <div class="pull-right">
                        <div class="btn-group" role="group" aria-label="...">
                            {{-- @if ($employee->isOnCall())
                                <a href="/admin/employees/oncall/clear" class="btn btn-danger">Clear On Call</a>
                            @else
                                <a href="/admin/employees/oncall/set/{{ $employee->id }}" class="btn btn-success">Set On Call</a>
                            @endif --}}
                            <a href="{{ route('admin.employees.edit', [$employee]) }}" class="btn btn-default">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="pull-right">
                    <a href="/admin/employees/create" class="btn btn-primary btn-lg">Create New Employee</a>
                </div>
            </div>
        </div>
    </div>
@endsection