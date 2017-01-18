@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-9">
            @include('pto._list')
        </div>
        <div class="col-md-3">
            @include('pto._form')
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            VIEW ONE PTO
        </div>
        <div class="col-md-3">
            PTO LEFT PER EMPLOEE
        </div>
    </div>
</div>
@endsection