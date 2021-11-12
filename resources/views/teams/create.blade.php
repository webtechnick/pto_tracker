@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>New Team</h1>

        {{ Form::open(['route' => 'admin.teams.store']) }}
            @include('teams/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Team</button>
            </div>
        {{ Form::close() }}
    </div>
@endsection