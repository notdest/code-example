
@extends('layouts.main')

@section('search')
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
        <div class="row">
            @php
                $streams = array_merge([ 0 =>'Все потоки'], \App\RssSource::$streams);
            @endphp
            <div class="col-2">
                <select class="form-control form-control-dark" name="stream">
                    @foreach ($streams as $k => $stream)
                        <option {{ (($k === $search->stream) ? 'selected':'') }} value="{{$k}}">{{$stream}}</option>
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
    @php
        $params  = http_build_query(['from' => $search->from,'to' => $search->to,])
    @endphp
    <h2>Статьи</h2>
    <div class="clearfix mb-3"  >
        <a href="/articles/download/?{!!$params !!}"><img src="/img/xlsx.png" style="height: 45px;" class="float-right"></a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th>Заголовок</th>
                <th>Автор</th>
                <th>Источник</th>
                <th>Дата публикации</th>
                <th>Лид</th>
                <th style="width: 165px;">Категория</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($articles as $article)
                <tr>
                    <td><a href="{!! $article->link !!}" target="_blank">{{ $article->title }}</a></td>
                    <td>{{ $article->author }}</td>
                    <td>{{ $article->source->name }}</td>
                    <td>{{ $article->pub_date }}</td>
                    <td>
                        <div style="height: 40px; width: 400px"  class="overflow-hidden">
                            {{ mb_substr( strip_tags($article->description),0 ,150) }}
                        </div>
                    </td>
                    <td>{{ $article->category }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $articles->withQueryString()->links() }}
    </div>

@endsection
