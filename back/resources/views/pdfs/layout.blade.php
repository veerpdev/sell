<!DOCTYPE html>
<html lang="en">

<head>
    <title>@yield('title', 'Document')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 20px;
            height: 100%;
            font-family: 'Poppins';
            font-size: 13px;
        }

        footer {
            position: absolute;
            bottom: 0px;
        }

        section {
            padding: 0;
            margin: 0;
        }

        strong {
            letter-spacing: 0.4px;
        }

        .light {
            color: gray;
            font-size: small;
        }

        .heading {
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .heading.upper {
            text-transform: uppercase;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>

    @yield('styles')
</head>

<body>
    @yield('content')
</body>

</html>