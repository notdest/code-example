@extends('layouts.main')

@section('content')

<h2>Настройки парсера Instagram</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (isset($success)&&$success)
    <div class="alert alert-success">
        Настройки сохранены
    </div>
@endif

<div style="max-width: 50em;">
    <form method="post" >
        <div class="form-group">
            <label for="config_login" >{{ $config->fieldName('login') }}</label>
            <input type="text" class="form-control" id="config_login" name="login" value="{{ $config->login }}">
        </div>

        <div class="form-group">
            <label for="config_password" >{{ $config->fieldName('password') }}</label>
            <input type="text" class="form-control" id="config_password" name="password" value="{{ $config->password }}">
        </div>

        <div class="form-group">
            <label for="config_session" >{{ $config->fieldName('session') }}</label>
            <input type="text" class="form-control" id="config_session" name="session" value="{{ $config->session }}">
        </div>

        <div class="form-group">
            <label for="config_enabled" >{{ $config->fieldName('enabled') }}</label>

            <div id="config_enabled">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="enabled" id="config_enabled1" value="0" {{ ($config->enabled == 0) ? 'checked':'' }}>
                    <span>Выключен</span>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="enabled" id="config_enabled2" value="1" {{ ($config->enabled != 0) ? 'checked':'' }}>
                    <span>Включен</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="config_proxy" >{{ $config->fieldName('proxy') }}</label>
            <input type="text" class="form-control" id="config_proxy" name="proxy" value="{{ $config->proxy }}">
        </div>

        @csrf
        <input type="submit" value="Сохранить" class="btn btn-success">
    </form>
</div>

@endsection
