
@extends('layouts.main')

@section('content')
    @php
        $params  = http_build_query((Array) $search);

        $translateSelect = [
            0 =>'Оригинальные заголовки',
            1 =>'Переведенные заголовки',
         ];
        $foreignSelect = [
            0 => 'Все страны',
            1 => 'Российские',
            2 => 'Зарубежные',
        ];
    @endphp
    <h2>Британский королевский дом</h2>

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

            <div class="col-2">
                <select class="form-control form-control-sm" name="translate">
                    @foreach ($translateSelect as $k => $name)
                        <option {{ (($k === $search->translate) ? 'selected':'') }} value="{{$k}}">{{$name}}</option>
                    @endforeach
                </select>
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
                @php
                    if($search->translate>0){
                        $title  = (strlen($article->title)>0)? $article->title : $article->foreign_title;
                        $prompt = ( (strlen($article->title)>0)&&(strlen($article->foreign_title)>0) ) ? $article->foreign_title : '';
                    }else{
                        $title  = (strlen($article->foreign_title)>0)? $article->foreign_title : $article->title;
                        $prompt = ( (strlen($article->title)>0)&&(strlen($article->foreign_title)>0) ) ? $article->title : '';
                    }
                @endphp
                <tr>
                    <td>
                        <a href="{!! $article->link !!}" target="_blank">{{ $title }}</a>
                        @if(strlen($prompt)>0)
                            <img src="/img/help.png" style="height: 1em;" data-toggle="tooltip" title="{{ $prompt }}">
                        @endif
                        @if(strlen($article->original_text)>0)
                            <a href="/articles/text/{{$article->id}}/" title="Текст статьи" class="ml-2"><img src="/img/text.png" style="height: 1.5em;" ></a>
                        @endif
                    </td>
                    <td>{{ $article->source->name }}</td>
                    <td>{{ $article->pub_date }}</td>
                    <td>{{ $article->category }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $articles->withQueryString()->links() }}
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
@endsection
