<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Flight REST API</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Mr+Dafoe&amp;family=Inter:wght@400;600;700&amp;display=swap" rel="stylesheet"/>
    <!-- Styles -->
    <style>
        html,
        body {
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
            font-family: 'Mr Dafoe', serif;
            font-size: 84px;
        }

        .title small {
            font-family: 'Raleway', sans-serif;
            font-size: 28px;
        }

        .title2 {
            font-size: 2.2rem;
        }

        .links>a {
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
        <div class="content">
            <div class="title m-b-md">
                Flight <small>REST API</small>
            </div>

            <div class="title2 m-b-md">
                <?php echo $version ?>
            </div>

            <div class="links">
                <a href="https://github.com/davchezt/rest-api" target="_blank">Repo</a>
                <a href="https://flightphp.com/">Flight</a>
                <a href="https://github.com/firebase/php-jwt">PHP JWT</a>
            </div>
        </div>
    </div>
</body>

</html>