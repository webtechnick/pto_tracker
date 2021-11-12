@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('admin.teams.delete', [$team]) }}" class="btn btn-danger btn-mini pull-right">Delete</a>

        <h1>Update Team</h1>

        {{ Form::model($team, ['route' => ['admin.teams.update', $team], 'method' => 'POST']) }}
            @include('teams/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Team</button>
            </div>
        {{ Form::close() }}
    </div>
@endsection