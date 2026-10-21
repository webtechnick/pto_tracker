@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>New Team</h1>

        {{ html()->form('POST', route('admin.teams.store'))->open() }}
            @include('teams/_form')
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Team</button>
            </div>
        {{ html()->form()->close() }}
    </div>
@endsection