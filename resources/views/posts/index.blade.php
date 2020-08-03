
@extends('layouts.main')

@section('content')

    <h2>Посты</h2>

    <div class="row">

        @foreach ($posts as $post)
            <div class="col-md-5 col-lg-3 col-12 m-2 card" style="max-height: 600px;">
                <a href="https://www.instagram.com/p/{!! $post->postId !!}/" target="_blank">
                    <img class="card-img-top" src="{!! $post->image !!}" alt="хз">
                </a>
                <h5 class="card-title mt-2">Алексей Навальный
                    <a href="https://www.instagram.com/navalny/"  target="_blank">
                        <small class="text-muted">(Instagram)</small>
                    </a>
                </h5>
                <h6 class="card-subtitle mb-2 text-muted">{{ $post->createdTime }}</h6>
                <div class="overflow-auto card-text">
                    {{ $post->text }}
                </div>
            </div>
        @endforeach

    </div>


    {{ $posts->links() }}
@endsection
