<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>WikiMoDex{{ isset($title) ? ' - ' . $title : '' }}</title>

    @stack('head')

    <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="/css/ScumsCyborg.css" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <style>
        body {
            color: white;
            background-color: #20172f !important;
        }
        .container { background: #20172f !important; }
        .card {
            background-color: #361b63 !important;
            border: 1px solid #100720;
        }
        .card-body {
            background-color: #20172f;
            color: white;
        }
        .bg-dark { background-color: #0c0616 !important; }
        .footer { background-color: #20172f; }
        input.form-control {
            color: white;
            background-color: #20172f;
        }
        .form-control:focus {
            color: white;
            background-color: #1a043f;
            border-color: #80bdff;
            box-shadow: 0 0 0 .2rem rgba(0,123,255,.25);
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { color: white; }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #aaa;
            border-radius: 3px;
            padding: 4px;
            background-color: transparent;
            color: white;
        }
        table.dataTable tbody tr { background-color: #343a40; }
    </style>
</head>
<body>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>

    @include('partials.nav')

    <main role="main" class="container" style="padding-top: 80px;">
        @yield('content')
    </main>

    <footer class="footer mt-auto py-3">
        <div class="container">
            <span class="text-muted">Questions? Contact <code>scooom</code> on Discord.</span>
        </div>
    </footer>
</body>
</html>
