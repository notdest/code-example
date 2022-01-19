@php
    $fields = \App\User::$fieldNames;
    $roles  = \App\User::$roleNames;
@endphp

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
        Пользователь сохранен
    </div>
@endif

<form method="post" >
<div class="row">

<div class="col block">
    <h4>Пользователь</h4>
    <div class="form-group">
        <label for="user_email" >{{ $fields["email"] }}<span class="red"> *</span></label>
        <input type="text" class="form-control {{ ($errors->has('email')) ? 'is-invalid':''}}" id="user_email" name="email" value="{{ $user->email }}">
        @if($errors->has('email'))
            <div class="invalid-feedback">
                {{ $errors->first('email') }}
            </div>
        @endif
    </div>

    <div class="form-group">
        <label for="user_password" >{{ $fields["password"] }}<span class="red"> *</span></label>
        <input type="password" class="form-control {{ ($errors->has('password')) ? 'is-invalid':''}}" id="user_password" name="password" >
        @if($errors->has('password'))
            <div class="invalid-feedback">
                {{ $errors->first('password') }}
            </div>
        @endif
    </div>

    <div class="form-group">
        <label for="user_blocked" >{{ $fields["blocked"] }}<span class="red"> *</span></label>

        <div id="user_blocked">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="blocked" id="user_blocked1" value="0" {{ ($user->blocked == 0) ? 'checked':'' }}>
                <span>Активен</span>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="blocked" id="user_blocked2" value="1" {{ ($user->blocked != 0) ? 'checked':'' }}>
                <span>Заблокирован</span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="user_role" >{{ $fields["role"] }}<span class="red"> *</span></label>

        <select class="form-control" id="user_role" name="role">
            @foreach($roles as $k => $role)
                <option {{ ($user->role == $k) ? 'selected':'' }} value="{{$k}}">{{$role}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="user_surname" >{{ $fields["surname"] }}</label>
        <input type="text" class="form-control" id="user_surname" name="surname" value="{{ $user->surname }}">
    </div>

    <div class="form-group">
        <label for="user_name" >{{ $fields["name"] }}</label>
        <input type="text" class="form-control" id="user_name" name="name" value="{{ $user->name }}">
    </div>

    <div class="form-group">
        <label for="user_department" >{{ $fields["department"] }}</label>
        <input type="text" class="form-control" id="user_department" name="department" value="{{ $user->department }}">
    </div>
</div>

<div class="col block">

    <h4>Доступ</h4>
    <div class="form-group">
        <label for="user_show_posts" >{{ $fields["show_posts"] }}</label>

        <div id="user_show_posts">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="show_posts" id="user_show_posts2" value="1" {{ ($user->show_posts != 0) ? 'checked':'' }}>
                <span>Открыт</span>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="show_posts" id="user_show_posts1" value="0" {{ ($user->show_posts == 0) ? 'checked':'' }}>
                <span>Закрыт</span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="user_show_articles" >{{ $fields["show_articles"] }}</label>

        <div id="user_show_articles">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="show_articles" id="user_show_articles2" value="1" {{ ($user->show_articles != 0) ? 'checked':'' }}>
                <span>Открыт</span>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="show_articles" id="user_show_articles1" value="0" {{ ($user->show_articles == 0) ? 'checked':'' }}>
                <span>Закрыт</span>
            </div>
        </div>
    </div>

</div>
</div>
    @csrf
    <input type="submit" value="Сохранить" class="btn btn-success">
    @if($user->role === \App\User::ROLE_ADMIN)
        <script type="text/javascript">
            function getToken(){
                let text            = 'Я уверен, что нужно заново сгенерировать токен';
                let confirmation    = prompt('Введите: "' + text +'"');
                let location        = document.location;

                if(confirmation === text){
                    location.replace(location.protocol + "//" + location.host + "/users/token/{{ $user->id }}/" );
                }
            }
        </script>

        <button type="button" class="btn btn-warning" onclick="getToken();" id="tokenButton">
            Заново сгенерировать токен
        </button>
    @endif
</form>
