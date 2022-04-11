@extends('layouts.main')

@section('content')

@php
    $fields = \App\RssSource::$fieldNames;
@endphp
    <h2>Конкуренты</h2>

    <div class="alert alert-warning" role="alert">
        Внимание! Добавление нового сайта всегда требует локальной отладки, команда ./artisan rss:import [id конкурента].
        При добавлении зарубежных сайтов для скачивания текста нужно писать отдельный адаптер, что сильно снижает надежность
        системы из-за меняющегося дизайна и рекламы в тексте.
    </div>

    <div class="container my-2 mr-0 text-right">
        <a href="/rss-sources/create/" class="btn btn-success">Добавить нового</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>{{ $fields['name'] }}</th>
                <th>{{ $fields['link'] }}</th>
                <th width="70%">{{ $fields['stream'] }}</th>
                <th>{{ $fields['active'] }}</th>
                <th>Статей</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($sources as $source)
                @php
                    $count = intval($statistic[$source->id] ?? 0) ;
                @endphp
                <tr>
                    <td>{{ $source->id }}</td>
                    <td><a href="/rss-sources/edit/{!! $source->id !!}/">{{ $source->name }}</a></td>
                    <td>
                        {{ $source->link }}
                        &nbsp;<a href="{{ $source->link }}" target="_blank"><img src="/img/external.png" style="height: 1em;"></a>
                    </td>
                    <td>
                        {{ implode(', ',$source->streams) }}
                    </td>
                    <td>
                        @if($source->active > 0)
                            <img class="icon" src="/img/checkmark.png">
                        @else
                            <img class="icon" src="/img/crossmark.png">
                        @endif
                    </td>
                    <td>
                        @if($count > 0)
                            {{$count}} &nbsp;<a href="/articles/?source={{ $source->id }}" ><img src="/img/loupe.png" style="height: 1em;"></a>
                        @else
                            0 <img src="/img/help.png" style="height: 1em;" data-toggle="tooltip" title="Или ещё не подгрузилось, или нужно программировать адаптер">
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $sources->links() }}
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
@endsection
