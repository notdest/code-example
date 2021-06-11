@php
    $fields = \App\Source::$fieldNames;
    $types  = \App\Source::$types;
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
        Аккаунт сохранен
    </div>
@endif

<div style="max-width: 50em;">
<form method="post" >

    <h4>Аккаунт</h4>

    <div class="form-group">
        <label for="source_code" >{{ $fields['code'] }}<span class="red"> *</span></label>
        <input type="text" class="form-control" id="source_code" name="code" value="{{ $source->code }}">
    </div>

    <div class="form-group">
        <label for="source_type" >{{ $fields["type"] }}</label>
        <select class="form-control" id="source_type" name="type">
            @foreach($types as $k => $type)
                <option {{ ($source->type == $k) ? 'selected':'' }} value="{{$k}}">{{$type}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="source_person" >{{ $fields["name"] }}</label>
        <select class="form-control" id="source_person" name="person">
            @foreach($persons as $person)
                <option {{ ($source->personId == $person->id) ? 'selected':'' }} value="{{$person->id}}">{{$person->name}}</option>
            @endforeach
        </select>
        <input style="width: 30em;" type="text" class="form-control" id="source_new_person" name="new_person" placeholder="Создать новую">
    </div>

    <div class="form-group">
        <label for="source_active" >{{ $fields['active'] }}</label>
        <div id="source_active">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="active" id="source_active1" value="0" {{ ($source->active == 0) ? 'checked':'' }}>
                <span>Выключен</span>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="active" id="source_active2" value="1" {{ ($source->active != 0) ? 'checked':'' }}>
                <span>Включен</span>
            </div>
        </div>
    </div>

    @csrf
    <input type="submit" value="Сохранить" class="btn btn-success">
</form>
</div>
