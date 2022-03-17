
@extends('layouts.main')

@section('content')
    @php
        $globalFormatter    = Date::createFromTimestamp($date);
        $formatter          = clone $globalFormatter;
        $params             = 'date='.date('Y-m-d',$date);

        $format     = function($number){
            return number_format( $number,0,'.',' ');
        }
    @endphp
    <h2>Статьи LiveInternet</h2>


    <div class="clearfix mb-3 mt-5"  style="max-width: 95em;">
        <span class="h3">Динамика по дням</span>
        <span class="float-right">
            <a href="/liveinternet-pages/?date={{ date('Y-m-d',strtotime('-1 day',$date)) }}" class="mr-4">⟵ {{ $formatter->subDay()->format('j F') }}</a> &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="/liveinternet-pages/download/?{!!$params !!}&table=day_all" class="mr-4"><img src="/img/xlsx_all.png" style="height: 45px;" ></a> &nbsp; &nbsp;
            <a href="/liveinternet-pages/download/?{!!$params !!}&table=day"><img src="/img/xlsx.png" style="height: 45px;" ></a>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm" style="max-width: 95em;">
            <thead class="thead-dark">
            <tr>
                <th>Сайт</th>
                <th style="text-align: center;">Статья</th>
                <th style="text-align: center;">{{ $formatter->addDay()->format('j F') }}</th>
                <th style="text-align: center;">{{ $formatter->subDay()->format('j F') }}</th>
                <th style="text-align: center;">{{ $formatter->subDay()->format('j F') }}</th>
                <th style="text-align: center;">{{ $formatter->subDay()->format('j F') }}</th>
                <th style="text-align: center;">{{ $formatter->subDay()->format('j F') }}</th>
                <th style="text-align: center;">{{ $formatter->subDay()->format('j F') }}</th>
                <th style="text-align: center;">{{ $formatter->subDay()->format('j F') }}</th>
            </tr>
            </thead>
            @foreach($yesterday as $row)

                    <tr>
                        <td>{{ \App\Http\Controllers\LiveinternetPagesController::$sites[$row->site] }}</td>
                        <td>{{ html_entity_decode($row->page) }}</td>
                        <td style="text-align: center;">{{ $format($row->views) }}</td>
                        <td style="text-align: center;">{{ isset($before[0][$row->page_hash]) ? $format($before[0][$row->page_hash]) : '' }}</td>
                        <td style="text-align: center;">{{ isset($before[1][$row->page_hash]) ? $format($before[1][$row->page_hash]) : '' }}</td>
                        <td style="text-align: center;">{{ isset($before[2][$row->page_hash]) ? $format($before[2][$row->page_hash]) : '' }}</td>
                        <td style="text-align: center;">{{ isset($before[3][$row->page_hash]) ? $format($before[3][$row->page_hash]) : '' }}</td>
                        <td style="text-align: center;">{{ isset($before[4][$row->page_hash]) ? $format($before[4][$row->page_hash]) : '' }}</td>
                        <td style="text-align: center;">{{ isset($before[5][$row->page_hash]) ? $format($before[5][$row->page_hash]) : '' }}</td>
                    </tr>

            @endforeach
            <tbody>

            </tbody>
        </table>
    </div>


    <div class="clearfix mb-3 mt-5"  style="max-width: 95em;">
        <span class="h3">Динамика по неделям</span>
        <a href="/liveinternet-pages/download/?{!!$params !!}&table=week"><img src="/img/xlsx.png" style="height: 45px;" class="float-right"></a>
    </div>
    @php
        $formatter      = clone $globalFormatter;
        $period         = $lastWeek->max('count');
        $previousWeek   = $previousWeek->keyBy(function ($item) {
                return $item->page_hash.'_'.$item->site;
        });
    @endphp

    <div class="table-responsive">
        <table class="table table-striped table-sm" style="max-width: 95em;">
            <thead class="thead-dark">
            <tr>
                <th>Сайт</th>
                <th style="text-align: center;">Статья</th>
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
                @php
                    $prevRow = isset($previousWeek[$row->page_hash.'_'.$row->site]) ? $previousWeek[$row->page_hash.'_'.$row->site]: null;
                @endphp
                <tr>
                    <td>{{ \App\Http\Controllers\LiveinternetPagesController::$sites[$row->site] }}</td>
                    <td>{{ html_entity_decode($row->page) }}</td>
                    <td style="text-align: center;" >{{ $format($row->views) }}</td>
                    <td style="text-align: center;" >
                        {{ $prevRow ? $format( $prevRow->views) : '' }}
                    </td>
                    <td style="text-align: center;">
                        @if( !$prevRow )

                        @elseif(($row->count === $period) && ($prevRow->count === $period))
                            {{ number_format( ($row->views - $prevRow->views)/$prevRow->views*100,2,'.',' ') }}
                        @else
                            {{ $row->count ."дн. - ".$prevRow->count."дн." }}
                        @endif
                    </td>
                </tr>

            @endforeach
            <tbody>

            </tbody>
        </table>
    </div>
    <p>Собрано дней: {{ $period }}</p>


    <div class="clearfix mb-3 mt-5"  style="max-width: 95em;">
        <span class="h3">Динамика по месяцам</span>
        <a href="/liveinternet-pages/download/?{!!$params !!}&table=month"><img src="/img/xlsx.png" style="height: 45px;" class="float-right"></a>
    </div>
    @php
        $formatter      = clone $globalFormatter;
        $periodLeft     = $lastMonth->max('count');
        $periodRight    = $previousMonth->max('count');
        $previousMonth  = $previousMonth->keyBy(function ($item) {
                return $item->page_hash.'_'.$item->site;
        });
    @endphp

    <div class="table-responsive">
        <table class="table table-striped table-sm" style="max-width: 95em;">
            <thead class="thead-dark">
            <tr>
                <th>Сайт</th>
                <th style="text-align: center;">Статья</th>
                <th style="text-align: center;">
                    {{ $formatter->subMonth()->day(1)->format('j F'). " - " .$formatter->format('t F')}}
                </th>
                <th style="text-align: center;">
                    {{ $formatter->subMonth()->format('j F')." - " .$formatter->format('t F')}}
                </th>
                <th style="text-align: center;">Динамика (%)</th>
            </tr>
            </thead>
            @foreach($lastMonth as $row)
                @php
                    $prevRow = isset($previousMonth[$row->page_hash.'_'.$row->site]) ? $previousMonth[$row->page_hash.'_'.$row->site]: null;
                @endphp
                <tr>
                    <td>{{ \App\Http\Controllers\LiveinternetPagesController::$sites[$row->site] }}</td>
                    <td>{{ html_entity_decode($row->page) }}</td>
                    <td style="text-align: center;" >{{ $format($row->views) }}</td>
                    <td style="text-align: center;" >
                        {{ $prevRow ? $format( $prevRow->views) : '' }}
                    </td>
                    <td style="text-align: center;">
                        @if( !$prevRow )

                        @elseif(($row->count === $periodLeft) && ($prevRow->count === $periodRight))
                            {{ number_format( ($row->views - $prevRow->views)/$prevRow->views*100,2,'.',' ') }}
                        @else
                            {{ $row->count ."дн. - ".$prevRow->count."дн." }}
                        @endif
                    </td>
                </tr>

            @endforeach
            <tbody>

            </tbody>
        </table>
    </div>
    <p>Собрано дней за {{ $formatter->addMonth()->format('F') }}: {{ $periodLeft }};
        за {{ $formatter->subMonth()->format('F') }}: {{ $periodRight }}</p>
    <br><br><br><br><br>
@endsection
