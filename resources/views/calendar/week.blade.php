@extends('layouts.main')
@php
    $start      = '';
    $today      = date('Y-m-d');

    $weekDays   = ['пн.','вт.','ср.','чт.','пт.','сб.','вс.'];
@endphp

@section('body-params') data-spy="scroll" data-target="#spy-navbar" data-offset="300" @endsection

@section('sticky-top')
    @php
        $isCurrentWeek  = $search->year.$search->week  === date("Y").intval(date("W"));

        $currentDay     = date('j');

        $prevYearWeeks  = intval(date('W',strtotime( ($search->year-1)."-12-31")));
        $thisYearWeeks  = intval(date('W',strtotime( ($search->year)."-12-31")));

        $prevWeek       = ($search->week-1)                     ?: $prevYearWeeks;
        $nextWeek       = ($search->week+1 >= $thisYearWeeks)   ? 1 : $search->week+1;
        $prevLink       = "/calendar/".
                                "?year=".(($search->week <= 1) ? $search->year - 1  : $search->year).
                                "&week=".(($search->week <= 1) ? $prevYearWeeks     : $search->week - 1 );
        $nextLink       = "/calendar/".
                                "?year=".(($search->week >= $thisYearWeeks) ? $search->year + 1 : $search->year).
                                "&week=".(($search->week >= $thisYearWeeks) ? 1                 : $search->week + 1 );

        $carbon     = Date::parse($weekStart);
        $weekPeriod = $carbon->format('j M').' - '.$carbon->addDays(6)->format('j M');
    @endphp
    <script type="text/javascript">
        function delightfulScroll(hash){
            window.scrollTo(0,   Math.round( $(hash).position().top ) - 200   );
        }

        @if($isCurrentWeek && isset($days[$currentDay]))
            $(document).ready(function (){
                window.scrollTo(0,   Math.round( $("#day_{{$currentDay}}").position().top ) - 200   );
            })
        @endif

        function deleteEvent(id){
            $.get( "/calendar/delete/"+id+"/", function() {
                $('#event_'+id).remove();
            });
        }
    </script>

    <h2>Календарь на неделю <small class="text-muted">{{ $weekPeriod.' '. $search->year }} г.</small></h2>
    <nav id="spy-navbar" class="navbar navbar-light bg-light mb-3">
        <a href="{!! $prevLink !!}">⟵ {{ $prevWeek }}-я неделя</a>

        <ul class="nav nav-pills">
            @foreach($days as $day)
                <li class="nav-item">
                    <a class="nav-link" style="padding: 0.4rem 0.3rem;{!! ($isCurrentWeek && ($day == $currentDay)) ? 'color: #ff0000;':'' !!}" href="#day_{{ $day }}" onclick="delightfulScroll(this.hash); return false;">{{ $day }}</a>
                </li>
            @endforeach
        </ul>

        <a href="{!! $nextLink !!}">{{ $nextWeek}}-я неделя ⟶</a>
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
        @endphp
        <div class="row mb-2 ml-1" id="event_{{ $event->id }}">
            <div class="col-5">{{$event->title}}</div>

            <div class="col-1 p-0">
                @php
                    if($event->end !== date('Y-m-d 23:59:59',strtotime($event->start)) ){
                        echo "до ".Date::parse($event->end)->format('j M. Y');
                    }
                @endphp
            </div>
        @can("admin")
            <div class="col-1">
                <a  href="/calendar/delete/{{$event->id}}/" onclick="deleteEvent({{ $event->id }});return false;">
                    <img class="icon" src="/img/trash.png" title="Удалить">
                </a>
            </div>
        @endcan
        </div>

    @endforeach

@endsection
