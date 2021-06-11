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
        Персона сохранена
    </div>
@endif

<div style="max-width: 50em;">
<form method="post" >

    <h4>Персона</h4>

    <div class="form-group">
        <label for="person_name" >Имя<span class="red"> *</span></label>
        <input type="text" class="form-control" id="person_name" name="name" value="{{ $person->name }}">
    </div>

    <div class="form-group">
        <label for="person_hidden" >Видимость</label>

        <div id="person_hidden">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="hidden" id="person_hidden1" value="0" {{ ($person->hidden == 0) ? 'checked':'' }}>
                <span>Показывать</span>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="hidden" id="person_hidden2" value="1" {{ ($person->hidden != 0) ? 'checked':'' }}>
                <span>Убрать подальше</span>
            </div>
        </div>
    </div>

    @csrf
    <input type="submit" value="Сохранить" class="btn btn-success">
</form>
</div>
