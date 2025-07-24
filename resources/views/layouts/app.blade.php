<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.header')
    @yield('head')
</head>
<body>
    <div id="app">
        @include('layouts._nav')

        @include('layouts.flashes')

        @yield('content')
    </div>

    <!-- Scripts -->
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
    <script src="/js/app.js"></script>
    <script src="/js/libs/jquery-ui.min.js"></script>
    @yield('scripts')
</body>
</html>
