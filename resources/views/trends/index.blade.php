
@extends('layouts.main')

@section('search')
    <script type="text/javascript">
        $( document ).ready(function() {
            $('input[name="from"]').daterangepicker({
                singleDatePicker: true ,
                timePicker: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss'
                }
            });
            $('input[name="to"]').daterangepicker({
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
    </form>
@endsection

@section('content')
    <h2>Тренды</h2>

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

    {{ $trends->withQueryString()->links() }}
@endsection
