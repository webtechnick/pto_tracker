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
                <currentday :admin="admin" :ptos="ptos" :holidays="holidays"></currentday>
            </div>

            <div class="row">
                <employeekey :employees="employees"></employeekey>
            </div>
        </div>
    </div>
</div>
@endsection