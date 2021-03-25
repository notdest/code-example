
@extends('layouts.main')

@section('content')
    @php
        $params  = http_build_query((Array) $search);
    @endphp
    <h2>Статьи конкурентов</h2>

    <script type="text/javascript">
        $( document ).ready(function() {
            $('input[name="from"]').daterangepicker({
                singleDatePicker: true ,
                autoApply: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss'
                }
            });
            $('input[name="to"]').daterangepicker({
                singleDatePicker: true ,
                autoApply: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY-MM-DD 23:59:59'
                }
            });
        });
    </script>
    <form method="get" class="col-md-10">
        <div class="row mb-2">

            <div class="col-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">С</span>
                    </div>
                    <input type="text" class="form-control" name="from"
                           aria-label="С" aria-describedby="basic-addon1" value="{!! $search->from !!}">
                </div>
            </div>

            <div class="col-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon2">По</span>
                    </div>
                    <input type="text" class="form-control" name="to"
                           aria-label="По" aria-describedby="basic-addon2" value="{!! $search->to !!}">
                </div>


            </div>

            @php
                $streams = [ '0' =>'Все потоки'];
                foreach (\App\RssSource::$streams as $k => $stream){
                    $streams[$k]    = $stream;  // array_merge() теряет ключи
                }
            @endphp
            <div class="col-2">
                <select class="form-control form-control-sm" name="stream">
                    @foreach ($streams as $k => $stream)
                        <option {{ (($k === $search->stream) ? 'selected':'') }} value="{{$k}}">{{$stream}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">

            <div class="col-4">
                <input type="text" class="form-control form-control-sm" name="searchQuery"
                       placeholder="Поисковой запрос" value="{!! $search->searchQuery !!}">
            </div>

            <div class="col">
                <input type="submit" value="Искать" class="btn btn-primary btn-sm">
            </div>
        </div>
    </form>

    <div class="clearfix mb-3"  >
        <a href="/articles/download/?{!!$params !!}"><img src="/img/xlsx.png" style="height: 45px;" class="float-right"></a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th>Заголовок</th>
                <th style="width: 140px;">Источник</th>
                <th style="width: 150px;">Дата публикации</th>
                <th style="width: 165px;">Категория</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($articles as $article)
                <tr>
                    <td><a href="{!! $article->link !!}" target="_blank">{{ $article->title }}</a></td>
                    <td>{{ $article->source->name }}</td>
                    <td>{{ $article->pub_date }}</td>
                    <td>{{ $article->category }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $articles->withQueryString()->links() }}
    </div>

@endsection
