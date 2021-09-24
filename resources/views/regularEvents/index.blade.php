@extends('layouts.main')
@php
    $categories = \App\Event::$categories;
    $start      = '';
    asort($categories);
    $options    = [ 0 => "- Категория -"] + $categories ;
@endphp

@section('content')

<h2>Редактирование событий</h2>
<script type="text/javascript">
    function confirmation(){
        return confirm('Удалить событие?');
    }


    $( document ).ready(function() {
        $('input[name="start"]').daterangepicker({
            timePickerSeconds: true,
            singleDatePicker: true ,
            timePicker: true,
            timePicker24Hour: true,
            locale: {
                format: 'YYYY-MM-DD HH:mm:ss'
            }
        });
        $('input[name="end"]').daterangepicker({
            timePickerSeconds: true,
            singleDatePicker: true ,
            timePicker: true,
            timePicker24Hour: true,
            locale: {
                format: 'YYYY-MM-DD HH:mm:ss'
            }
        });
    });
</script>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (isset($success)&&$success)
    <div class="alert alert-success">
        События сохранены
    </div>
@endif


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

        <div class="col-2">
            {{$categories[intval($event->category)]}}
            <a href="/regular-events/delete/{{$event->id}}/" onclick="return confirmation()">
                <img class="icon" src="/img/trash.png">
            </a>
        </div>
    </div>

@endforeach
<div style="max-width: 65em;
            border: solid;
            padding: 20px;
            border-radius: 10px;
            margin-top: 40px;
            border-width: thin;">

    <form method="post" action="/regular-events/save/">
        <div class="row">
            <div class="col-4">
                <select name="category" class="form-control ">
                    @foreach($options as $k => $option)
                        <option value="{{$k}}" >{{$option}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-8">
                <input type="text" class="form-control" name="title" placeholder="Заголовок события">
            </div>
        </div>
        <div class="row mt-3">

            <div class="col-4">
                <input type="text" class="form-control" name="start" value="{{date('Y-m-d 00:00:00')}}">
            </div>
            <div class="col-4">
                <input type="text" class="form-control" name="end" value="{{date('Y-m-d 23:59:59')}}">
            </div>
            <div class="col-2">
                @csrf
                <input type="submit" value="Сохранить" class="btn btn-success">
            </div>
        </div>
    </form>
</div>

<div style="max-width: 65em;
            border: solid;
            padding: 20px;
            border-radius: 10px;
            margin-top: 40px;
            border-width: thin;">

    <form method="post" action="/regular-events/tsv/">
        <div class="form-group">
            <label for="config_notes" >События в TSV формате</label> &nbsp; (&laquo;2021-04-24[tab]В США патентуется газированная вода&raquo;, только современные года)
            <textarea class="form-control" id="config_notes" name="tsv"  rows="15"></textarea>
        </div>

        <div class="row">
            <div class="col-4">
                <select name="category" class="form-control ">
                    @foreach($options as $k => $option)
                        <option value="{{$k}}" >{{$option}}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-2">
                @csrf
                <input type="submit" value="Сохранить" class="btn btn-success">
            </div>
        </div>
    </form>
</div>
<br>

@endsection
