@php
    $fields     = \App\RssSource::$fieldNames;
    $streams    = \App\RssSource::$streams;

    $adapters   = scandir(app_path('Console/Commands/rss_adapters/'));
    $adapters   = array_filter($adapters,function($v){ return !in_array($v,['..','.']);});
    $adapters   = array_map(function ($v){return str_replace('.php','',$v);},$adapters);
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
        Конкурент сохранен
    </div>
@endif
<div style="max-width: 50em;">
<form method="post" >

    <div class="form-group">
        <label for="source_name" >{{ $fields['name'] }}</label>
        <input type="text" class="form-control" id="source_name" name="name" value="{{ $source->name }}">
    </div>

    <div class="form-group">
        <label for="source_link" >{{ $fields['link'] }}</label>
        <input type="text" class="form-control" id="source_link" name="link" value="{{ $source->link }}">
    </div>

    <div class="form-group">
        <label for="source_stream" >{{ $fields['stream'] }}</label>
        <select multiple class="form-control" id="source_stream" name="streams[]" aria-describedby="streamHelpBlock">
            @foreach($streams as $k => $stream)
                <option value="{{$k}}" {{ ($k & $source->stream) ? "selected" : "" }}>{{$stream}}</option>
            @endforeach
        </select>
        <small id="streamHelpBlock" class="form-text text-muted">
            Ctrl для множественного выделения.
        </small>
    </div>

    <div class="form-group">
        <label for="source_adapter" >{{ $fields['adapter'] }}</label>
        <select class="form-control form-control-sm" id="source_adapter" name="adapter">
            @foreach($adapters as $adapter)
                <option value="{{$adapter}}" {{ ($source->adapter == $adapter) ? 'selected':'' }}>{{$adapter}}</option>
            @endforeach
        </select>
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
