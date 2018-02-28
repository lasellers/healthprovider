<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Health Provider</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    @if (Route::has('login'))
        <div class="top-right links">
            @if (Auth::check())
                <a href="{{ url('/home') }}">Home</a>
            @else
                <a href="{{ url('/login') }}">Login</a>
                <a href="{{ url('/register') }}">Register</a>
            @endif
        </div>
    @endif

    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="title m-b-md">
                    Health Provider
                </div>

                <div class="links">
                    <a href="/nhc.csv">NHC.csv</a>
                    <a href="/nhc.dup.csv">NHC.dup.csv</a>
                    <a href="/nhc.html">NHC.html</a>
                    <a href="/hhc.csv">HHC.csv</a>
                    <a href="/hhc.dup.csv">HHC.dup.csv</a>
                    <a href="/hhc.html">HHC.html</a>
                    <a href="/invalid_domains.txt">invalid_domains.txt</a>
                </div>


            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <br><br><br>
                To run artisan tasks:<br>

                <code>
                    docker-compose up --build<br>
                    docker exec -it healthprovider_app_1 /bin/bash<br>
                </code>

                <code>
                    php artisan healthprovider:hhc<br>
                    php artisan healthprovider:nhc<br>
                    php artisan healthprovider:status<br>
                </code>

            </div>
        </div>

        <br><br><br>

        <div class="links">
            <a href="https://github.com/lasellers/healthprovider">https://github.com/lasellers/healthprovider</a>
        </div>

    </div>
</div>
</body>
</html>
