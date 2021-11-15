
@extends('layouts.main')

@section('content')
    <script type="text/javascript">
        let translated  = {{ ( (strlen($article->translated_text)>0)&&(strlen($article->title)>0) ) ? 'true': 'false' }};

        function changeLanguage(original){
            if(original){
                $("#translatedTitle").hide();
                $("#translatedText").hide();
                $("#originalTitle").show()
                $("#originalText").show();
            }else{
                if(!translated){
                    $('input[name=original]').attr("disabled",true);
                    $.get("/articles/translate/{{ $article->id }}/",function (data){
                        if(data.success === true){
                            $("#translatedTitle").html(data.translatedTitle);
                            $("#translatedText").html(data.translatedText);
                        }else{
                            $("#translatedTitle").html("Ошибка перевода");
                            $("#translatedText").html("Ошибка перевода");
                        }
                        translated  = true;
                        $('input[name=original]').attr("disabled",false);
                    });
                }
                $("#originalTitle").hide()
                $("#originalText").hide();
                $("#translatedTitle").show();
                $("#translatedText").show();
            }
        }

    </script>
    <h2>Текст статьи</h2>

    <a href="javascript:history.back();" class="btn btn-link mr-4">⟵ Назад</a>

    <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
        <label class="btn btn-secondary active">
            <input type="radio" name="original" autocomplete="off" checked value="0" onchange="changeLanguage(true);">Оригинал
        </label>
        <label class="btn btn-secondary">
            <input type="radio" name="original" autocomplete="off" value="1" onchange="changeLanguage(false);">Перевод
        </label>
    </div>

    <h3 class="mb-4" id="originalTitle">{{$article->foreign_title}}</h3>
    <h3 class="mb-4" id="translatedTitle" style="display: none">{{$article->title}}</h3>


    <div style="max-width: 70em;" id="originalText">
        {!! str_replace("\n","<br>\n",$article->original_text) !!}
    </div>

    <div style="max-width: 70em;display: none;" id="translatedText" >
        {!! str_replace("\n","<br>\n",$article->translated_text) !!}
    </div>
@endsection
