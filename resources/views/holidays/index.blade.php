@extends('layouts.app')
@section('content')
    <div class="container">
        @foreach($holidays as $holiday)
            <div class="panel panel-default">
                <div class="panel-body">
                    <span class="name">{{ $holiday->title }}</span>
                    <small>{{ $holiday->date }}</small>
                    @if ($holiday->isHalfDay())
                        <span class="glyphicon glyphicon-adjust"></span>
                    @endif
                    <div class="pull-right">
                        <div class="btn-group" role="group" aria-label="...">
                            <a href="{{ route('admin.holidays.edit', [$holiday]) }}" class="btn btn-default">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="pull-right">
                    <a href="/admin/holidays/create" class="btn btn-primary btn-lg">Create New Holiday</a>
                    <a href="/admin/holidays/bulk" class="btn btn-primary btn-lg">Bulk Holiday</a>
                </div>
            </div>
        </div>
    </div>
@endsection