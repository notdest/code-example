<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <title>Парсер</title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <script src="/js/jquery-3.6.0.min.js" ></script>
    <script type="text/javascript" src="/js/moment.min.js"></script>
    <script type="text/javascript" src="/js/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/daterangepicker.css" />
    <!-- Custom styles for this template -->
    <link href="/css/dashboard.css" rel="stylesheet">
</head>

<body @yield('body-params')>
<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="#">Independent Media</a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    @hasSection('search')
        @yield('search')
    @endif
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="sidebar-sticky pt-3">
                @include('layouts.menu')

                @auth
                    <div style="margin: 10px 0 0 20px">
                        {{ Auth::user()->name }} ( <a href="#" onclick="document.getElementById('logout-form').submit();">Выйти</a> )
                        <form id="logout-form" action="/logout/" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                @endauth

            </div>
        </nav>

        @hasSection('sticky-top')
            <div class="col-md-9 ml-sm-auto col-lg-10 fixed-top  sticky-top-header border-bottom">
                @yield('sticky-top')
            </div>
        @endif

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">

            @yield('content')

        </main>
    </div>
</div>

<script src="/js/bootstrap.bundle.min.js" ></script>


</body></html>
