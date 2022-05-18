@extends('layouts.main')
@php
    $categories = \App\Event::$categories;
    asort($categories);
    $options    = [ 0 => "- Категория -"] + $categories ;

    $start      = '';
    $today      = date('Y-m-d');
    $weekDays   = ['пн.','вт.','ср.','чт.','пт.','сб.','вс.'];
@endphp

@section('body-params') data-spy="scroll" data-target="#spy-navbar" data-offset="300" @endsection

@section('search')

    <form method="get" class="col-md-10">
        <div class="row">

            <div class="col-2">
                <select name="category" class="form-control form-control-dark">
                    @foreach($options as $k => $option)
                        <option value="{{$k}}" {{ ($search->category == $k ) ? "selected" : "" }}>{{$option}}</option>
                    @endforeach
                </select>
                <input type="hidden" name="year"  value="{{ $search->year }}">
                <input type="hidden" name="month" value="{{ $search->month }}">
            </div>

            <div class="col">
                <input type="submit" value="Искать" class="btn btn-primary">
            </div>
        </div>
    </form>
@endsection

@section('sticky-top')
    @php
        $months     = [     // Именительный падеж проще руками перечислить, чем вытянуть из карбона
            1 => 'Январь',      2 => 'Февраль',     3 => 'Март',     4  => 'Апрель',      5 => 'Май',     6  => 'Июнь',
            7 => 'Июль',        8 => 'Август',      9 => 'Сентябрь', 10 => 'Октябрь',    11 => 'Ноябрь',  12 => 'Декабрь',
            0 => 'Декабрь',     13 => 'Январь',
        ];

        $isCurrentMonth = $search->year.$search->month  === date("Yn");
        $currentMonth   = $months[$search->month].' '.$search->year;
        $currentDay     = date('j');

        $prevMonth      = $months[$search->month-1].' '.( ($search->month < 2) ? $search->year - 1 : $search->year);
        $nextMonth      = $months[$search->month+1].' '.( ($search->month > 11) ? $search->year + 1 : $search->year);
        $prevLink       = "/calendar/month/?category={$search->category}&year=".
                                (($search->month < 2) ? $search->year - 1 : $search->year)."&month=".
                                (($search->month < 2) ? 12 : $search->month - 1 );
        $nextLink       = "/calendar/month/?category={$search->category}&year=".
                                (($search->month > 11) ? $search->year + 1 : $search->year)."&month=".
                                (($search->month > 11) ? 1 : $search->month + 1 );
    @endphp
    <script type="text/javascript">
        function delightfulScroll(hash){
            window.scrollTo(0,   Math.round( $(hash).position().top ) - 200   );
        }

        @if($isCurrentMonth && isset($days[$currentDay]))
            $(document).ready(function (){
                window.scrollTo(0,   Math.round( $("#day_{{$currentDay}}").position().top ) - 200   );
            })
        @endif

        function addEvent(id,regular){
            $.get( "/calendar/add/"+id+"/"+regular.toString()+'/', function( data ) {
                let jsID = id + (regular ? 'r':'');
                $('#addLink_'+jsID).hide();
                $('#delLink_'+jsID).show().data('id', data.id);
            });
        }

        function deleteEvent(id,regular){
            let jsID    = id + (regular ? 'r':'');
            let eventId = $('#delLink_'+jsID).data('id');

            $.get( "/calendar/delete/"+eventId+"/", function() {
                $('#addLink_'+jsID).show();
                $('#delLink_'+jsID).hide().data('id', 0);
            });
        }
    </script>

    <h2>Календарь событий {{ $currentMonth }}</h2>
    <nav id="spy-navbar" class="navbar navbar-light bg-light mb-3">
        <a href="{!! $prevLink !!}">⟵ {{ $prevMonth }}</a>

        <ul class="nav nav-pills">
            @foreach($days as $day)
                <li class="nav-item">
                    <a class="nav-link" style="padding: 0.4rem 0.3rem;{!! ($isCurrentMonth && ($day == $currentDay)) ? 'color: #ff0000;':'' !!}" href="#day_{{ $day }}" onclick="delightfulScroll(this.hash); return false;">{{ $day }}</a>
                </li>
            @endforeach
        </ul>

        <a href="{!! $nextLink !!}">{{ $nextMonth }} ⟶</a>
    </nav>
@endsection

@section('content')
    <div style="height: 12em;">  <!-- спейсер, чтобы подвинуть вниз относительно заголовка-->  </div>

    @foreach($events as $event)
        @php
            if($start !== $event->start){
                $start      = $event->start;
                $date       = Date::parse($event->start);
                $todayMark  = ($date->format('Y-m-d') === $today) ? ' <small class="text-muted">(Сегодня)</small>' : '';
                echo    '<h3 class="mt-5 mb-3" id="day_'.$date->format('j').'">'.
                            '<small class="text-muted">'.$weekDays[$date->weekday()].'</small> '.$date->format('j F').$todayMark.
                        "</h3>";
            }

            $regular    = $event->regular ? "true": "false";
            $jsId       = $event->regular ? $event->id."r": $event->id;
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
            <div class="col-1">
                <a  href="/calendar/add/{{$event->id.'/'.$regular}}/"
                    {!! ($event->weekEvent > 0) ? 'style="display: none;"':"" !!}
                    id="addLink_{{$jsId}}"
                    onclick="addEvent({{$event->id.','.$regular}});return false;">

                        <img class="icon" src="/img/add.png" title="Добавить">
                </a>

                <a  href="/calendar/delete/{{$event->weekEvent}}/"
                    {!! ($event->weekEvent <= 0) ? 'style="display: none;"':"" !!}
                    id="delLink_{{$jsId}}"
                    data-id="{{$event->weekEvent}}"
                    onclick="deleteEvent({{$event->id.','.$regular}});return false;">

                        <img class="icon" src="/img/trash.png" title="Удалить">
                </a>
            </div>
        </div>

    @endforeach

@endsection
