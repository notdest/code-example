
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
                <select name="source" class="form-control form-control-dark">
                    <option value="0">Instagram</option>
                    <option value="1" {{ ($search->source == 1 ) ? "selected" : "" }}>Facebook</option>
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

    <h2>Посты</h2>

    <div class="row">

        @foreach ($posts as $post)
            <div class="col-md-5 col-lg-3 col-12 m-2 card" style="max-height: 600px;">
                <a href="https://www.instagram.com/p/{!! $post->postId !!}/" target="_blank">
                    <img class="card-img-top" src="{!! $post->image !!}" >
                </a>
                <h5 class="card-title mt-2">{{ $post->name }}
                    <a href="https://www.instagram.com/{!! $post->code !!}/"  target="_blank">
                        <small class="text-muted">({{ $post->type }})</small>
                    </a>
                </h5>
                <h6 class="card-subtitle mb-2 text-muted">{{ $post->createdTime }}</h6>
                <div class="overflow-auto card-text">
                    {{ $post->text }}
                </div>
            </div>
        @endforeach

    </div>


    {{ $posts->withQueryString()->links() }}
@endsection
