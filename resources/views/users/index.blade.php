@extends('layouts.main')


@section('content')
    @php
    $fields = \App\User::$fieldNames;
    $roles  = \App\User::$roleNames;
    @endphp
    <h2>Список пользователей</h2>

    <div class="container my-2 mr-0 text-right">
        <a href="/users/create/" class="btn btn-success">Добавить нового</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
            <tr>
                <th>{{ $fields['id'] }}</th>
                <th>{{ $fields['email'] }}</th>
                <th>{{ $fields['surname'] }}</th>
                <th width="100%">{{ $fields['name'] }}</th>
                <th>{{ $fields['blocked'] }}</th>
                <th>{{ $fields['role'] }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td><a href="/users/edit/{!! $user->id !!}/">{{ $user->email }}</td>
                    <td>{{ $user->surname }}</td>
                    <td>{{ $user->name }}</td>
                    <td>
                        @if($user->blocked > 0)
                            <img class="icon" src="/img/crossmark.png">
                        @else
                            <img class="icon" src="/img/checkmark.png">
                        @endif
                    </td>
                    <td>{{ $roles[$user->role] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $users->withQueryString()->links() }}
    </div>

@endsection
