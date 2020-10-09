@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-9">
            <input id="inputyear" name="inputyear" type="hidden" value="{{ $year }}">
            <input id="inputteam" name="inputteam" type="hidden" value="{{ $team }}">

            <div class="center">
                @if ($team)
                    <a class="pull-left" href="/{{ $team }}/{{ $year - 1 }}"><span class="glyphicon glyphicon-backward"></span></a>
                    <a class="pull-right" href="/{{ $team }}/{{ $year + 1 }}"><span class="glyphicon glyphicon-forward"></span></a>
                @else
                    <a class="pull-left" href="/{{ $year - 1 }}"><span class="glyphicon glyphicon-backward"></span></a>
                    <a class="pull-right" href="/{{ $year + 1 }}"><span class="glyphicon glyphicon-forward"></span></a>
                @endif
                <h1 v-text="year"></h1>

            </div>

            <calendar :year="year" :ptos="ptos" :holidays="holidays"></calendar>
        </div>
        <div class="col-md-3">
            @include('pto._form')

            <div class="row">
                <currentday :year="year" :admin="admin" :ptos="ptos" :holidays="holidays"></currentday>
            </div>

            <div class="row">
                @include('pto._teams')
            </div>

            <div class="row">
                @include('pto._employee_key')
                <!-- <employeekey :employees="employees"></employeekey> -->
            </div>

            <div class="row">
                @include('pto._calendar_legend')
            </div>
        </div>
    </div>
</div>
@endsection