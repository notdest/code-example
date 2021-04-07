@extends('layouts.main')


@section('content')
    @php
        $categorySelect[0]  = ' ';
        $categorySelect[-1] = '[Удалить найденные]';
        $categorySelect     = $categorySelect + \App\Article::$categories ;

    @endphp
    <h2>Категории на сайтах конкурентов</h2>

    <script type="text/javascript">
        function confirmation(){
            return confirm('Удалить этот псевдоним категории?');
        }
    </script>

    <h4>Учтенные категории</h4>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th style="min-width: 3em;">Id</th>
                <th style="min-width: 9em;">Источник</th>
                <th width="100%">Их категория</th>
                <th style="min-width: 8em;">Наш аналог</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->source->name }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->categoryName }}</td>
                    <td>
                        <a href="/rss-category/delete/{{ $category->id }}/" onclick="return confirmation()">
                            <img class="icon" src="/img/trash.png">
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $categories->withQueryString()->links() }}
    </div>

    <h4 style="margin-top: 4em;">Неопознанные категории</h4>

    <form method="post" action="/rss-category/write/" class="col-md-6">
    @php( $i = 1)
    @foreach($unknowns as $source => $cats)
        @foreach($cats as $cat)

            <div class="row mb-2">
                <div class="col-4">
                    {{$sources[$source]}}
                </div>

                <div class="col-4">
                    {{$cat}}
                </div>

                <div class="col-4">
                    <input type="hidden" name="source[{{ $i }}]" value="{{ $source }}">
                    <input type="hidden" name="theirCategory[{{ $i }}]" value="{{ $cat }}">

                    <select class="form-control form-control-sm" name="ourCategory[{{ $i }}]">
                        @foreach ($categorySelect as $k => $category)
                            <option value="{{$k}}">{{$category}}</option>
                        @endforeach
                    </select>
                </div>

            </div>
            @php( $i++ )
        @endforeach
    @endforeach
        <div class="row mb-2">
            <div class="col-10"> &nbsp; </div>
            <div class="col-2 text-right">
                <input type="submit" value="Записать" class="btn btn-success btn-sm">
            </div>
        </div>
        @csrf
    </form>

    <h4 style="margin-top: 4em;">Статьи с неопознанными категориями</h4>

    <div class="table-responsive mt-4 mb-5">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th>Заголовок</th>
                <th style="width: 140px;">Источник</th>
                <th style="width: 150px;">Дата публикации</th>
                <th style="width: 165px;">Распознанные категории</th>
                <th style="width: 165px;">Нераспознанные категории</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($articles as $article)
                <tr>
                    <td><a href="{!! $article->link !!}" target="_blank">{{ $article->title }}</a></td>
                    <td>{{ $article->source->name }}</td>
                    <td>{{ $article->pub_date }}</td>
                    <td>{{ $article->category }}</td>
                    <td>{{ $article->unknown_categories }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
