<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/app.css"/>
    <title>@yield('title', 'TicketBeast')</title>
    @include('scripts.app')
</head>
<body>
<div id="app">
    @yield('body')
</div>

@stack('beforeScripts')
<script src="/js/app.js"></script>
@stack('afterScripts')
</body>
</html>
