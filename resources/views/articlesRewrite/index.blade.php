@extends('layouts.main')

@section('content')
    @php
        $translateSelect = [
            0 =>'Оригинальные заголовки',
            1 =>'Переведенные заголовки',
         ];

        $sourceSelect = [ '' => 'Все источники'];
        foreach ($sources as $source) {
            $sourceSelect[$source]  = $source;
        }
    @endphp
    <h2>Статьи под рерайт</h2>



    <form method="get" class="col-md-10">
        <div class="row mt-2">
            <div class="col-2">
                <select class="form-control form-control-sm" name="source">
                    @foreach ($sourceSelect as $k => $name)
                        <option {{ (($k === $search->source) ? 'selected':'') }} value="{{$k}}">{{$name}}</option>
                    @endforeach
                </select>
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
        <a href="/articles-rewrite/translate-titles/" title="Перевести заголовки"><img src="/img/translate.png" style="height: 45px;" class="float-right"></a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th>Заголовок</th>
                <th style="width: 170px;">Источник</th>
                <th style="width: 105px;">Действие</th>
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
                    </td>
                    <td>
                        {{ $article->source }}
                    </td>
                    <td>
                        <a href="/articles-rewrite/text/{{$article->id}}/" title="Текст статьи" class="ml-2"><img src="/img/text.png" style="height: 1.5em;" ></a>

                    </td>
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
