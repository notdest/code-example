
@extends('layouts.main')

@section('content')

    <h2>Персоны</h2>

    <div class="container my-2 mr-0 text-right">
        <button type="button" class="btn btn-success btn-lg">Добавить новую</button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th style="width: 35px;">#</th>
                <th>Имя</th>
                <th style="width: 210px;">Действие</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($persons as $person)
                <tr>
                    <td>{{ $person->id }}</td>
                    <td>{{ $person->name }}</td>
                    <td>
                        <button type="button" class="btn btn-outline-info btn-sm">Редактировать</button>
                        @can("post-editor")
                            <a type="button" class="btn btn-outline-danger btn-sm" href="/persons/delete/{{ $person->id }}/">Удалить</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $persons->links() }}
    </div>
@endsection
