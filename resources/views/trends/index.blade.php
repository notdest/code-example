
@extends('layouts.main')

@section('search')
    <script type="text/javascript">
        $( document ).ready(function() {
            $('input[name="from"][type="text"]').daterangepicker({
                singleDatePicker: true ,
                timePicker: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss'
                }
            });
            $('input[name="to"][type="text"]').daterangepicker({
                singleDatePicker: true ,
                timePicker: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss'
                }
            });
        });
    </script>
    <form method="get" class="col-md-10">
        <div class="row">
            <div class="col-2">
                <select name="feed" class="form-control form-control-dark">
                        <option value="0"> - страна - </option>

                    @foreach(\App\Trend::NAMES as $key => $name)
                        <option value="{{$key}}" {{ ($search->feed == $key ) ? "selected" : "" }}>{{$name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-2">
                <input type="text" class="form-control form-control-dark" name="from" value="{!! $search->from !!}">
            </div>

            <div class="col-2">
                <input type="text" class="form-control form-control-dark" name="to" value="{!! $search->to !!}">
            </div>

            <div class="col">
                <input type="submit" value="Искать" class="btn btn-primary">
            </div>
        </div>
        <input type="hidden" name="sorting" value="{{ $search->sorting }}">
    </form>
@endsection

@section('content')
    @php
        $params  = http_build_query((Array) $search);
    @endphp
    <h2>Google Trends</h2>

        <div class="row mb-2">
            <div class="col-1 pl-4 border-bottom" style="line-height: 1.8em; font-weight: bold; font-size: 1.2em;">
                Сортировать
            </div>
            <div class="col-4 border-bottom">
                <form method="get">
                    @foreach($search as $name => $value)
                        @if($name !== "sorting")
                            <input type="hidden" name="{{$name}}" value="{{$value}}">
                        @endif
                    @endforeach
                    <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
                        <label class="btn btn-secondary active">
                            <input type="radio" name="sorting" autocomplete="off" onchange="$(this).parents('form').submit()"
                                    {{ ($search->sorting === \App\Trend::SORTING_DATE) ? "checked" : "" }}
                                    value="{{\App\Trend::SORTING_DATE}}">По дате
                        </label>
                        <label class="btn btn-secondary">
                            <input type="radio" name="sorting" autocomplete="off" onchange="$(this).parents('form').submit()"
                                    {{ ($search->sorting === \App\Trend::SORTING_TRAFFIC) ? "checked" : ""}}
                                    value="{{\App\Trend::SORTING_TRAFFIC}}">По запросам
                        </label>
                    </div>
                </form>
            </div>
            <div class="col-2 border-bottom pb-2">
                <a href="/trends/download/?{!!$params !!}"><img src="/img/xlsx.png" style="height: 45px;" class="float-right"></a>
            </div>
        </div>

    @foreach ($trends as $trend)

    <div class="row mb-3">
        <div class="col-6">
            <h5 class="mb-0">{{ $trend->title }}</h5>
            @foreach($trend->news as $news)
                <a href="{!! $news->news_item_url !!}" target="_blank">{!! $news->news_item_title !!}</a>
                {{$news->news_item_source}}<br>
            @endforeach

        </div>
        <div class="col-2">
            <h5 class="mb-0 mt-2" style="line-height: 0.5;">{{ round($trend->traffic/1000) }} тыс.+ </h5>
            <small class="text-muted">запросов</small>
            <p class="mb-0 mt-1">
            @php
                $hours  = round((time()-strtotime($trend->pubDate))/3600);
                if($hours > 48){
                    echo date("Y.m.d",strtotime($trend->pubDate));
                }else{
                    echo "$hours ч. назад";
                }
            @endphp
            </p>
        </div>
    </div>

    @endforeach
<br><br><br>
@endsection
