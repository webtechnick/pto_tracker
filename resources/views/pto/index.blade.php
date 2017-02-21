@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-9">

            <calendar :ptos="ptos" :holidays="holidays"></calendar>

        </div>
        <div class="col-md-3">
            @include('pto._form')

            <div class="row">
                <currentday :ptos="ptos" :holidays="holidays"></currentday>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            VIEW DAY PTO
        </div>
        <div class="col-md-3">
            PTO LEFT PER EMPLOEE
        </div>
    </div>
</div>
@endsection