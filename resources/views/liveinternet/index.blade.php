
@extends('layouts.main')

@section('content')
    @php
        $globalFormatter    = Date::createFromTimestamp($date);
        $formatter          = clone $globalFormatter;
    @endphp
    <h2>LiveInternet</h2>

    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <a class="nav-link {{ ($tab === 'zen') ? 'active': '' }}" aria-current="page" href="/liveinternet/">Переходы из Дзен</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab === 'yandex') ? 'active': '' }}" href="/liveinternet/?tab=yandex">Переходы из Яндекс</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab === 'social') ? 'active': '' }}" href="/liveinternet/?tab=social">Переходы из соцсетей</a>
        </li>
    </ul>

    <h3>Динамика дня</h3>

    <div class="table-responsive">
        <table class="table table-striped table-sm" style="max-width: 70em;">
            <thead class="thead-dark">
            <tr>
                <th>Сайт</th>
                <th style="text-align: center;">{{ $formatter->format('j F') }}</th>
                <th style="text-align: center;">{{ $formatter->subDay()->format('j F') }}</th>
                <th style="text-align: center;">Динамика (%)</th>
            </tr>
            </thead>
                @foreach($yesterday as $site => $views)
                    @if(isset($before[$site]))
                        <tr>
                            <td>{{$site}}</td>
                            <td style="text-align: center;">{{ number_format($views,0,'.',' ')}}</td>
                            <td style="text-align: center;">{{ number_format($before[$site],0,'.',' ') }}</td>
                            <td style="text-align: center;">
                                {{ number_format((($views-$before[$site])/$before[$site])*100,2,'.',' ') }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            <tbody>

            </tbody>
        </table>
    </div>

    <h3 class="mt-3">Сумма за неделю</h3>
    @php
        $formatter      = clone $globalFormatter;
        $period         = $lastWeek->max('count');
        $previousWeek   = $previousWeek->keyBy('site');
    @endphp
    <div class="table-responsive">
        <table class="table table-striped table-sm" style="max-width: 70em;">
            <thead class="thead-dark">
            <tr>
                <th>Сайт</th>
                <th style="text-align: center;">
                    {{ $formatter->subDay(6)->format('j F'). " - " .$formatter->addDay(6)->format('j F')}}
                </th>
                <th style="text-align: center;">
                    {{ $formatter->subDay(13)->format('j F')." - " .$formatter->addDay(6)->format('j F')}}
                </th>
                <th style="text-align: center;">Динамика (%)</th>
            </tr>
            </thead>
            @foreach($lastWeek as $row)
                @if(isset($previousWeek[$row->site]))
                    <tr>
                        <td>{{$row->site}}</td>
                        <td style="text-align: center;">{{ number_format($row->views,0,'.',' ')}}</td>
                        <td style="text-align: center;">{{ number_format($previousWeek[$row->site]->views,0,'.',' ') }}</td>
                        <td style="text-align: center;">
                            @if( ($row->count === $period) && ($previousWeek[$row->site]->count === $period) )
                                {{ number_format(( ($row->views - $previousWeek[$row->site]->views)/$previousWeek[$row->site]->views)*100,2,'.',' ') }}
                            @else
                                {{$row->count ."дн. - ".$previousWeek[$row->site]->count."дн."}}
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
            <tbody>

            </tbody>
        </table>
    </div>
    <p>Собрано дней: {{ $period }}</p>

    <h3 class="mt-4">Сумма за месяц</h3>
    @php
        $formatter      = clone $globalFormatter;
        $period         = $lastMonth->max('count');
        $previousMonth  = $previousMonth->keyBy('site');
    @endphp
    <div class="table-responsive">
        <table class="table table-striped table-sm" style="max-width: 70em;">
            <thead class="thead-dark">
            <tr>
                <th>Сайт</th>
                <th style="text-align: center;">
                    {{ $formatter->subDay(29)->format('j F'). " - " .$formatter->addDay(29)->format('j F')}}
                </th>
                <th style="text-align: center;">
                    {{ $formatter->subDay(59)->format('j F')." - " .$formatter->addDay(29)->format('j F')}}
                </th>
                <th style="text-align: center;">Динамика (%)</th>
            </tr>
            </thead>
            @foreach($lastMonth as $row)
                @if(isset($previousMonth[$row->site]))
                    <tr>
                        <td>{{$row->site}}</td>
                        <td style="text-align: center;">{{ number_format($row->views,0,'.',' ')}}</td>
                        <td style="text-align: center;">{{ number_format($previousMonth[$row->site]->views,0,'.',' ') }}</td>
                        <td style="text-align: center;">
                            @if( ($row->count === $period) && ($previousMonth[$row->site]->count === $period) )
                                {{ number_format(( ($row->views - $previousMonth[$row->site]->views)/$previousMonth[$row->site]->views)*100,2,'.',' ') }}
                            @else
                                {{$row->count ."дн. - ".$previousMonth[$row->site]->count."дн."}}
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
            <tbody>

            </tbody>
        </table>
    </div>
    <p>Собрано дней: {{ $period }}</p>
    <br><br><br><br><br>

@endsection
