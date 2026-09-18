<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('template/img/logo.svg') }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'EBRS') }}</title>
    <link rel="stylesheet" href="{{ asset('template/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/ebrs.css') }}">
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    @livewireStyles
    @stack('styles')
    <script src="{{ asset('template/assets/js/app.js') }}"></script>
</head>
<body>
    @include('layouts.partials.sidebar')
    <div class="sidebar-backdrop"></div>
    @include('layouts.partials.header')
    @include('layouts.partials.settings')

    <main class="app-main">
        @include('layouts.partials.role-simulation')
        @include('layouts.partials.alerts')
        @yield('content')
        @include('layouts.partials.footer')
    </main>

    @include('layouts.partials.scripts')
</body>
</html>
