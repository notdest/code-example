
@extends('layouts.main')

@section('search')
    <script type="text/javascript">
        $( document ).ready(function() {
            $('input[name="from"]').daterangepicker({
                singleDatePicker: true ,
                timePicker: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss'
                }
            });
            $('input[name="to"]').daterangepicker({
                singleDatePicker: true ,
                timePicker: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss'
                }
            });
        });
    </script>
    <form method="get" class="col-md-10">
        <div class="row">
            <div class="col-2">
                <select name="person" class="form-control form-control-dark">
                    <option value="0"> - персона - </option>

                    @foreach ($persons as $person)
                        <option value="{!! $person->id !!}" {{ ($search->person == $person->id ) ? "selected" : "" }}>
                            {{ $person->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-2">
                <input type="text" class="form-control form-control-dark" name="from" value="{!! $search->from !!}">
            </div>

            <div class="col-2">
                <input type="text" class="form-control form-control-dark" name="to" value="{!! $search->to !!}">
            </div>

            <div class="col">
                <input type="submit" value="Искать" class="btn btn-primary">
            </div>
        </div>
    </form>
@endsection

@section('content')

    <h2>Сторис{{ $enabled ? '' : " (Выключен)" }}</h2>

    <div class="row">

        @foreach ($stories as $story)
            <div class="col-md-4 col-lg-2 col-12 m-2 card" style="max-height: 650px;">
                @if($story->type == \App\Http\Controllers\StoryController::PHOTO)
                    <img class="card-img-top" src="{!! $story->image !!}" style="max-height: 565px;object-fit: contain;">
                @else
                    <video controls height="565" poster="{!! $story->image !!}" preload="none">
                        <source src="{!! $story->video !!}"  type="video/mp4">
                        Sorry, your browser doesn't support embedded videos.
                    </video>
                @endif
                <h5 class="card-title mt-2">
                    @php
                        $search->person = $story->personId;
                    @endphp
                    <a href="https://www.instagram.com/{!! $story->code !!}/"  target="_blank">{{ $story->name }}</a>
                    <a href="/stories/?{!! http_build_query($search) !!}" ><img src="/img/loupe.png" style="height: 1em;"></a>
                </h5>
                <h6 class="card-subtitle mb-2 text-muted">{{ $story->createdTime }} ({{($story->type == \App\Http\Controllers\StoryController::PHOTO) ? "Фото":"Видео ".$story->duration." сек." }})</h6>
                <div class="overflow-auto card-text">

                </div>
            </div>
        @endforeach

    </div>


    {{ $stories->withQueryString()->links() }}
@endsection
