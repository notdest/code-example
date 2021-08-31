@extends('layouts.main')
@php
    $categories = \App\Event::$categories;
    $start      = '';
    asort($categories);
    $options    = [ 0 => "- Категория -"] + $categories ;
@endphp

@section('search')

    <form method="get" class="col-md-10">
        <div class="row">

            <div class="col-2">
                <select name="category" class="form-control form-control-dark">
                    @foreach($options as $k => $option)
                        <option value="{{$k}}" {{ ($search->category == $k ) ? "selected" : "" }}>{{$option}}</option>
                    @endforeach
                </select>
            </div>

            <div class="col">
                <input type="submit" value="Искать" class="btn btn-primary">
            </div>
        </div>
    </form>
@endsection

@section('content')
    <h2>Календарь событий</h2>

    @foreach($events as $event)
        @php
            if($start !== $event->start){
                $start = $event->start;
                echo '<h3 class="mt-5 mb-3">'.Date::parse($event->start)->format('j F')."</h3>";
            }
        @endphp
        <div class="row mb-2 ml-1">
            <div class="col-5">{{$event->title}}</div>

            <div class="col-1 p-0">
                @php
                    if($event->end !== date('Y-m-d 23:59:59',strtotime($event->start)) ){
                        echo "до ".Date::parse($event->end)->format('j M. Y');
                    }
                @endphp
            </div>

            <div class="col-2"> {{$categories[intval($event->category)]}}  </div>
        </div>

    @endforeach

@endsection
