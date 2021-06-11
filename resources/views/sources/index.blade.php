
@extends('layouts.main')

@section('content')
@php
    $fields = \App\Source::$fieldNames;
@endphp
    <h2>Аккаунты в соцсетях</h2>

    <div class="container my-2 mr-0 text-right">
        <a href="/sources/create/" class="btn btn-success">Добавить новый</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th style="width: 35px;">#</th>
                <th>{{ $fields['code'] }}</th>
                <th style="width: 75%;">{{ $fields['name'] }}</th>
                <th class="text-center">{{ $fields['type'] }}</th>
                <th class="text-center">{{ $fields['active'] }}</th>
                <th class="text-center">Действие</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($sources as $source)
                <tr>
                    <td>{{ $source->id }}</td>
                    <td>
                        @can("post-editor")
                            <a href="/sources/edit/{{$source->id}}">{{ $source->code }}</a>
                        @endcan
                        @cannot("post-editor")
                            {{ $source->code }}
                        @endcannot
                    </td>
                    <td>{{ $source->person->name }}</td>
                    <td class="text-center">{{ $source->type }}</td>
                    <td class="text-center">
                        @if($source->active > 0)
                            <img class="icon" src="/img/checkmark.png">
                        @else
                            <img class="icon" src="/img/crossmark.png">
                        @endif
                    </td>
                    <td class="text-center">
                        @can("post-editor")
                            @if($source->active > 0)
                                <a href="/sources/deactivate/{{$source->id}}"><img class="icon" src="/img/hide.png" title="Деактивировать"></a>
                            @else
                                <a href="/sources/activate/{{$source->id}}"><img class="icon" src="/img/show.png" title="Активировать"></a>
                            @endif
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $sources->links() }}
    </div>
@endsection
