@extends('layouts.main')

@section('content')
    <h2>Статьи под рерайт</h2>

    <div class="clearfix mb-3"  >
        <a href="/articles-rewrite/translate-titles/" title="Перевести заголовки"><img src="/img/translate.png" style="height: 45px;" class="float-right"></a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th>Заголовок</th>
                <th style="width: 165px;">Действие</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($articles as $article)
                @php
                    $title  = (strlen($article->title)>0)? $article->title : $article->foreign_title;
                    $prompt = ( (strlen($article->title)>0)&&(strlen($article->foreign_title)>0) ) ? $article->foreign_title : '';
                @endphp
                <tr>
                    <td>
                        <a href="{!! $article->link !!}" target="_blank">{{ $title }}</a>
                        @if(strlen($prompt)>0)
                            <img src="/img/help.png" style="height: 1em;" data-toggle="tooltip" title="{{ $prompt }}">
                        @endif
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
