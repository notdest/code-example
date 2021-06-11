
@extends('layouts.main')

@section('content')

    <h2>Персоны</h2>

    <div class="container my-2 mr-0 text-right">
        <a href="/persons/create/" class="btn btn-success">Добавить новую</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th style="width: 35px;">#</th>
                <th>Имя</th>
                <th style="width: 120px;">Действие</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($persons as $person)
                <tr {!! ($person->hidden) ? 'class="hidden"':'' !!}>
                    <td>{{ $person->id }}</td>
                    <td>{{ $person->name }}</td>
                    <td>
                    @can("post-editor")
                        @if($person->id>0)
                                <a href="/persons/edit/{{$person->id}}"><img class="icon" src="/img/edit.svg" title="Редактировать"></a>
                            @if($person->hidden)
                                <a href="/persons/show/{{$person->id}}"><img class="icon" src="/img/show.png" title="Показывать на прежнем месте"></a>
                            @else
                                <a href="/persons/hide/{{$person->id}}"><img class="icon" src="/img/hide.png" title="Убрать подальше"></a>
                            @endif
                        @endif
                    @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $persons->links() }}
    </div>
@endsection
