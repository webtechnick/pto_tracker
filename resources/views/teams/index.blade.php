@extends('layouts.app')
@section('content')
    <div class="container">
        @foreach($teams as $team)
            <div class="panel panel-default">
                <div class="panel-body">
                    <span class="name">{{ $team->name }}</span>
                    <div class="pull-right">
                        <div class="btn-group" role="group" aria-label="...">
                            <a href="{{ route('admin.teams.delete', [$team]) }}" class="btn btn-danger">Remove Team</a>
                            <a href="{{ route('admin.teams.edit', [$team]) }}" class="btn btn-default">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="pull-right">
                    <a href="/admin/teams/create" class="btn btn-primary btn-lg">Create New Team</a>
                </div>
            </div>
        </div>
    </div>
@endsection