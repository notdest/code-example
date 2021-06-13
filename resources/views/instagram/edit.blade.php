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

        <div class="form-group">
            <label for="config_emails" >{{ $config->fieldName('emails') }}</label>
            <input type="text" class="form-control" id="config_emails" name="emails" value="{{ $config->emails }}" placeholder="Имя1|adress1@mail.ru,Имя2|adress2@mail.ru">
            <a href="/instagram/check-email/">Выслать проверочные E-mail</a> (нажимать после сохранения)
        </div>

        <script type="text/javascript">
            function checkSubscribed(){
                $("#subscribedButton").attr('disabled','disabled');
                $.get( "/instagram/check-subscribed/", function( data ) {
                    $("#subscribedButton").removeAttr('disabled');
                    $( "#subscribedCount" ).text(data);
                });
            }
        </script>
        <div class="form-group">
            <button type="button" class="btn btn-dark" onclick="checkSubscribed();" id="subscribedButton">
                Проверить количество подписанных
            </button>
            <span id="subscribedCount" class="ml-3">..... из .....</span>
        </div>

        <div class="form-group">
            <label for="config_enableSubscription" >{{ $config->fieldName('enableSubscription') }}</label>

            <div id="config_enableSubscription">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="enableSubscription" id="config_enableSubscription1" value="0" {{ ($config->enableSubscription == 0) ? 'checked':'' }}>
                    <span>Выключен</span>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="enableSubscription" id="config_enableSubscription2" value="1" {{ ($config->enableSubscription != 0) ? 'checked':'' }}>
                    <span>Включен</span>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            function showLostPosts(){
                $.get( "/instagram/show-lost-posts/", function( data ) {
                    $( "#lostPosts" ).text(data);
                });
            }

            function checkLostPosts(){
                let confirmed  = confirm('Это ресурсоёмкая задача, запускаем?');
                if (confirmed){
                    $("#checkLostButton").attr('disabled','disabled');
                    $.get( "/instagram/check-lost-posts/");
                }
            }
        </script>
        <div class="form-group">
            <button type="button" class="btn btn-dark" onclick="checkLostPosts()" id="checkLostButton" {{ $lostChecking ? 'disabled' : '' }}>
                Проверка пропущенных постов
            </button>
            <button type="button" class="btn btn-dark" onclick="showLostPosts()" >
                Вывод последней проверки
            </button>
        </div>
        <pre id="lostPosts"> </pre>

        @csrf
        <input type="submit" value="Сохранить" class="btn btn-success">
    </form>
</div>

@endsection
